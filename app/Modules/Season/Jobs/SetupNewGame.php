<?php

namespace App\Modules\Season\Jobs;

use App\Modules\Competition\Services\CountryConfig;
use App\Modules\Finance\Services\BudgetProjectionService;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Season\DTOs\SeasonTransitionData;
use App\Modules\Season\Services\SeasonSetupPipeline;
use App\Modules\Transfer\Services\ContractService;
use App\Modules\Player\Services\InjuryService;
use App\Modules\Player\Services\PlayerDevelopmentService;
use App\Modules\Player\Services\PlayerTierService;
use App\Modules\Player\Services\PlayerValuationService;
use App\Modules\Season\Processors\LeagueFixtureProcessor;
use App\Modules\Season\Processors\StandingsResetProcessor;
use App\Support\ExternalData;
use App\Support\Money;
use App\Models\ClubProfile;
use App\Models\Competition;
use App\Models\CompetitionEntry;
use App\Models\CompetitionTeam;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\TeamReputation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SetupNewGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    private bool $usedTemplates = false;
    private Carbon $currentDate;

    public function __construct(
        public string $gameId,
        public string $teamId,
        public string $competitionId,
        public string $season,
        public string $gameMode,
    ) {
        $this->onQueue('setup');
    }

    public function handle(
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
        SeasonSetupPipeline $setupPipeline,
        LeagueFixtureProcessor $fixtureProcessor,
        StandingsResetProcessor $standingsProcessor,
        BudgetProjectionService $budgetProjectionService,
    ): void {
        $game = Game::find($this->gameId);
        if (!$game) {
            return;
        }

        $isRepairRun = $game->isSetupComplete();
        $userSquadExists = GamePlayer::where('game_id', $this->gameId)
            ->where('team_id', $this->teamId)
            ->exists();

        if ($isRepairRun && $userSquadExists) {
            return;
        }

        DB::transaction(function () use ($game, $isRepairRun, $contractService, $developmentService, $setupPipeline, $fixtureProcessor, $standingsProcessor, $budgetProjectionService) {
            $this->currentDate = $game->current_date ?? Carbon::parse("{$this->season}-08-15");

            // Pre-load all reference data (2 queries instead of ~4,600)
            $allTeams = Team::whereNotNull('external_id')->get()->keyBy('external_id');
            $allPlayers = Player::all()->keyBy('external_id');

            // Step 1: Copy competition team rosters into per-game table
            $this->copyCompetitionTeamsToGame();

            // Step 1b: Initialize per-game reputation records for all teams
            $this->initializeTeamReputations();
    
            // Step 2: Initialize game players (template-based or fallback)
            $this->initializeGamePlayersFromTemplates($allTeams, $allPlayers, $contractService, $developmentService);
            $this->backfillMissingGamePlayersFromReferenceData($allTeams, $allPlayers, $contractService, $developmentService);

            if ($isRepairRun) {
                app(PlayerTierService::class)->recomputeAllTiersForGame($this->gameId);

                if ($game->isCareerMode()) {
                    $budgetProjectionService->generateProjections($game->refresh());
                }

                return;
            }

            // Step 3: Run shared setup processors
            if ($this->gameMode === Game::MODE_CAREER) {
                // Career mode: run all 4 shared processors (fixtures, standings, budget, cups/Swiss)
                $swissPotData = $this->buildSwissPotData($allTeams);

                $data = new SeasonTransitionData(
                    oldSeason: '0',
                    newSeason: $this->season,
                    competitionId: $this->competitionId,
                    isInitialSeason: true,
                    metadata: $swissPotData ? [SeasonTransitionData::META_SWISS_POT_DATA => $swissPotData] : [],
                );

                $setupPipeline->run($game->refresh(), $data);

                // Initialize players for Swiss format competitions (non-template path only)
                if (!$this->usedTemplates) {
                    $this->initializeSwissFormatPlayers($allTeams, $allPlayers, $contractService, $developmentService);
                }
            } else {
                // Non-career mode: only fixtures + standings (no budget/cups)
                $data = new SeasonTransitionData(
                    oldSeason: '0',
                    newSeason: $this->season,
                    competitionId: $this->competitionId,
                    isInitialSeason: true,
                );

                $fixtureProcessor->process($game, $data);
                $standingsProcessor->process($game, $data);
            }

            // Compute tiers for players when templates weren't used (fallback + Swiss)
            if (!$this->usedTemplates) {
                app(PlayerTierService::class)->recomputeAllTiersForGame($this->gameId);
            }

            // Mark setup as complete
            Game::where('id', $this->gameId)->update(['setup_completed_at' => now()]);

            // Record activation event
            app(\App\Modules\Season\Services\ActivationTracker::class)
                ->record($game->user_id, \App\Models\ActivationEvent::EVENT_SETUP_COMPLETED, $this->gameId, $this->gameMode);

            // Notify the user that the summer transfer window is open
            if ($this->gameMode === Game::MODE_CAREER) {
                app(NotificationService::class)->notifyTransferWindowOpen($game->refresh(), 'summer');
            }
        });
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Initial game setup failed', [
            'game_id' => $this->gameId,
            'team_id' => $this->teamId,
            'competition_id' => $this->competitionId,
            'season' => $this->season,
            'error' => $exception?->getMessage(),
            'trace' => $exception?->getTraceAsString(),
        ]);
    }

    private function copyCompetitionTeamsToGame(): void
    {
        // Idempotency: skip if already done
        if (CompetitionEntry::where('game_id', $this->gameId)->exists()) {
            return;
        }

        $rows = CompetitionTeam::where('season', $this->season)
            ->get()
            ->map(fn ($ct) => [
                'game_id' => $this->gameId,
                'competition_id' => $ct->competition_id,
                'team_id' => $ct->team_id,
                'entry_round' => $ct->entry_round ?? 1,
            ])
            ->toArray();

        foreach (array_chunk($rows, 100) as $chunk) {
            CompetitionEntry::insert($chunk);
        }
    }

    /**
     * Initialize per-game reputation records for all teams with competition entries.
     * Copies the static ClubProfile reputation as the starting point.
     * Applies a division bonus for lower-tier teams in top-division leagues.
     */
    private function initializeTeamReputations(): void
    {
        // Idempotency: skip if already done
        if (TeamReputation::where('game_id', $this->gameId)->exists()) {
            return;
        }

        $game = Game::find($this->gameId);
        $countryCode = $game->country ?? 'ES';

        // Load competition entries with their competition tier
        $entries = CompetitionEntry::where('game_id', $this->gameId)
            ->whereHas('competition', fn ($q) => $q->where('country', $countryCode))
            ->get();

        $teamIds = $entries->pluck('team_id')->unique();

        $clubProfiles = ClubProfile::whereIn('team_id', $teamIds)
            ->pluck('reputation_level', 'team_id');

        // Build a map of team_id => lowest competition tier (1 = top division)
        $competitionTiers = Competition::whereIn('id', $entries->pluck('competition_id')->unique())
            ->pluck('tier', 'id');

        $teamCompetitionTier = [];
        foreach ($entries as $entry) {
            $tier = $competitionTiers[$entry->competition_id] ?? 99;
            if (!isset($teamCompetitionTier[$entry->team_id]) || $tier < $teamCompetitionTier[$entry->team_id]) {
                $teamCompetitionTier[$entry->team_id] = $tier;
            }
        }

        $divisionBonus = (int) config('reputation.division_bonus', 25);

        $rows = [];
        foreach ($teamIds as $teamId) {
            $level = $clubProfiles[$teamId] ?? ClubProfile::REPUTATION_LOCAL;
            $points = TeamReputation::pointsForTier($level);

            // Apply division bonus for Modest/Local teams in tier 1
            $competitionTier = $teamCompetitionTier[$teamId] ?? 99;
            if ($competitionTier === 1 && in_array($level, [ClubProfile::REPUTATION_MODEST, ClubProfile::REPUTATION_LOCAL])) {
                $points += $divisionBonus;
            }

            $rows[] = [
                'id' => Str::uuid()->toString(),
                'game_id' => $this->gameId,
                'team_id' => $teamId,
                'reputation_level' => $level,
                'base_reputation_level' => $level,
                'reputation_points' => $points,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            TeamReputation::insert($chunk);
        }
    }

    /**
     * Build Swiss pot data from JSON for all Swiss competitions (initial season only).
     *
     * @return array<string, array<array{id: string, pot: int, country: string}>>
     */
    private function buildSwissPotData(Collection $allTeams): array
    {
        $countryConfig = app(CountryConfig::class);
        $game = Game::find($this->gameId);
        $countryCode = $game->country ?? 'ES';

        $swissIds = $countryConfig->swissFormatCompetitionIds($countryCode);
        $potData = [];

        foreach ($swissIds as $competitionId) {
            $teamsFilePath = base_path("data/{$this->season}/{$competitionId}/teams.json");
        if (!file_exists($teamsFilePath)) {
            continue;
        }

        $teamsData = ExternalData::decodeJsonFile($teamsFilePath);
        $clubs = $teamsData['clubs'] ?? [];

            $drawTeams = [];
            foreach ($clubs as $club) {
                $externalId = ExternalData::clubExternalId($club);
                if (!$externalId) {
                    continue;
                }

                $team = $allTeams->get($externalId);
                if (!$team) {
                    continue;
                }

                $drawTeams[] = [
                    'id' => $team->id,
                    'pot' => $club['pot'] ?? 4,
                    'country' => $club['country'] ?? 'XX',
                ];
            }

            if (!empty($drawTeams)) {
                $potData[$competitionId] = $drawTeams;
            }
        }

        return $potData;
    }

    /**
     * Initialize players for Swiss format competitions (fallback path only).
     * Skipped when templates are used since all players are already loaded.
     */
    private function initializeSwissFormatPlayers(
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): void {
        $countryConfig = app(CountryConfig::class);
        $game = Game::find($this->gameId);
        $countryCode = $game->country ?? 'ES';

        $swissIds = $countryConfig->swissFormatCompetitionIds($countryCode);

        foreach ($swissIds as $competitionId) {
            $teamsFilePath = base_path("data/{$this->season}/{$competitionId}/teams.json");
        if (!file_exists($teamsFilePath)) {
            continue;
        }

        $teamsData = ExternalData::decodeJsonFile($teamsFilePath);
        $clubs = $teamsData['clubs'] ?? [];
            $minimumWage = $contractService->getMinimumWageForCompetition($competitionId);

            foreach ($clubs as $club) {
                $externalId = ExternalData::clubExternalId($club);
                if (!$externalId) {
                    continue;
                }

                $team = $allTeams->get($externalId);
                if (!$team) {
                    continue;
                }

                // Skip teams that already have game players (e.g., Spanish teams from ESP1)
                if (GamePlayer::where('game_id', $this->gameId)->where('team_id', $team->id)->exists()) {
                    continue;
                }

                $playersData = $club['players'] ?? [];
                $playerRows = [];

                foreach ($playersData as $playerData) {
                    $row = $this->prepareGamePlayerRow($team, $playerData, $minimumWage, $allPlayers, $contractService, $developmentService, $this->currentDate);
                    if ($row) {
                        $playerRows[] = $row;
                    }
                }

                foreach (array_chunk($playerRows, 100) as $chunk) {
                    GamePlayer::insert($chunk);
                }
            }
        }
    }

    /**
     * Initialize game players from pre-computed templates, falling back to
     * the old per-player computation if templates don't exist.
     */
    private function initializeGamePlayersFromTemplates(
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): void {
        // Idempotency: skip if players already exist
        if (GamePlayer::where('game_id', $this->gameId)->exists()) {
            return;
        }

        // Always build squads from each club's source JSON so every club
        // reflects the roster currently defined in data/2025/*/teams.json.
        // This keeps manual per-club editing deterministic and avoids stale
        // or incomplete template data leaving clubs with empty squads.
        $this->usedTemplates = false;
        $this->initializeGamePlayers($allTeams, $allPlayers, $contractService, $developmentService);
    }

    // =====================================================================
    // Fallback methods — used when game_player_templates table is empty
    // =====================================================================

    /**
     * Initialize game players for all teams, following the config-driven
     * dependency order: playable tiers → transfer pool → continental.
     */
    private function initializeGamePlayers(
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): void {
        $countryConfig = app(CountryConfig::class);
        $game = Game::find($this->gameId);
        $countryCode = $game->country ?? 'ES';

        $competitionIds = $countryConfig->playerInitializationOrder($countryCode);
        $continentalIds = $countryConfig->continentalSupportIds($countryCode);

        foreach ($competitionIds as $competitionId) {
            if (in_array($competitionId, $continentalIds)) {
                continue;
            }

            $this->initializeGamePlayersForCompetition(
                $competitionId,
                $allTeams,
                $allPlayers,
                $contractService,
                $developmentService,
            );
        }
    }

    private function initializeGamePlayersForCompetition(
        string $competitionId,
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): void {
        $basePath = base_path("data/{$this->season}/{$competitionId}");
        $teamsFilePath = "{$basePath}/teams.json";

        if (file_exists($teamsFilePath)) {
            $clubs = $this->loadClubsFromTeamsJson($teamsFilePath);
        } else {
            $clubs = $this->loadClubsFromTeamPoolFiles($basePath);
        }

        if (empty($clubs)) {
            return;
        }

        $minimumWage = $contractService->getMinimumWageForCompetition($competitionId);
        $playerRows = [];

        foreach ($clubs as $club) {
            $externalId = ExternalData::clubExternalId($club);
            if (!$externalId) {
                continue;
            }

            $team = $allTeams->get($externalId);
            if (!$team) {
                continue;
            }

            $playersData = $club['players'] ?? [];
            foreach ($playersData as $playerData) {
                $row = $this->prepareGamePlayerRow($team, $playerData, $minimumWage, $allPlayers, $contractService, $developmentService, $this->currentDate);
                if ($row) {
                    $playerRows[] = $row;
                }
            }
        }

        foreach (array_chunk($playerRows, 100) as $chunk) {
            GamePlayer::insert($chunk);
        }
    }

    /**
     * Some seeded template sets can be incomplete for specific clubs.
     * If that happens, rebuild only the missing teams from their reference JSON.
     */
    private function backfillMissingGamePlayersFromReferenceData(
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): void {
        $missingTeamIds = CompetitionEntry::where('game_id', $this->gameId)
            ->pluck('team_id')
            ->unique()
            ->reject(fn (string $teamId) => GamePlayer::where('game_id', $this->gameId)->where('team_id', $teamId)->exists())
            ->values();

        if ($missingTeamIds->isEmpty()) {
            return;
        }

        $existingPlayerIds = GamePlayer::where('game_id', $this->gameId)
            ->pluck('player_id')
            ->flip()
            ->toArray();

        $competitionIds = CompetitionEntry::where('game_id', $this->gameId)
            ->pluck('competition_id')
            ->unique()
            ->values();

        foreach ($competitionIds as $competitionId) {
            if ($missingTeamIds->isEmpty()) {
                break;
            }

            $insertedTeamIds = $this->initializeMissingGamePlayersForCompetition(
                $competitionId,
                $missingTeamIds->all(),
                $existingPlayerIds,
                $allTeams,
                $allPlayers,
                $contractService,
                $developmentService,
            );

            if (!empty($insertedTeamIds)) {
                $missingTeamIds = $missingTeamIds
                    ->reject(fn (string $teamId) => in_array($teamId, $insertedTeamIds, true))
                    ->values();
            }
        }
    }

    /**
     * Initialize only the clubs that are still missing from the current game.
     *
     * @param  array<string>  $missingTeamIds
     * @param  array<string, bool>  $existingPlayerIds
     * @return array<string>
     */
    private function initializeMissingGamePlayersForCompetition(
        string $competitionId,
        array $missingTeamIds,
        array &$existingPlayerIds,
        Collection $allTeams,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
    ): array {
        $basePath = base_path("data/{$this->season}/{$competitionId}");
        $teamsFilePath = "{$basePath}/teams.json";

        if (file_exists($teamsFilePath)) {
            $clubs = $this->loadClubsFromTeamsJson($teamsFilePath);
        } else {
            $clubs = $this->loadClubsFromTeamPoolFiles($basePath);
        }

        if (empty($clubs)) {
            return [];
        }

        $minimumWage = $contractService->getMinimumWageForCompetition($competitionId);
        $playerRows = [];
        $insertedTeamIds = [];

        foreach ($clubs as $club) {
            $externalId = ExternalData::clubExternalId($club);
            if (!$externalId) {
                continue;
            }

            $team = $allTeams->get($externalId);
            if (!$team || !in_array($team->id, $missingTeamIds, true)) {
                continue;
            }

            foreach ($club['players'] ?? [] as $playerData) {
                $row = $this->prepareGamePlayerRow($team, $playerData, $minimumWage, $allPlayers, $contractService, $developmentService, $this->currentDate);
                if (!$row || isset($existingPlayerIds[$row['player_id']])) {
                    continue;
                }

                $playerRows[] = $row;
                $existingPlayerIds[$row['player_id']] = true;
            }

            if (!empty($club['players'])) {
                $insertedTeamIds[] = $team->id;
            }
        }

        foreach (array_chunk($playerRows, 100) as $chunk) {
            GamePlayer::insert($chunk);
        }

        return array_values(array_unique($insertedTeamIds));
    }

    private function prepareGamePlayerRow(
        Team $team,
        array $playerData,
        int $minimumWage,
        Collection $allPlayers,
        ContractService $contractService,
        PlayerDevelopmentService $developmentService,
        Carbon $currentDate,
    ): ?array {
        $player = $this->resolveReferencePlayer($playerData, $allPlayers, $currentDate);
        if (!$player) {
            return null;
        }

        $contractUntil = null;
        if (!empty($playerData['contract'])) {
            try {
                $contractUntil = Carbon::parse($playerData['contract'])->toDateString();
            } catch (\Exception $e) {
                // Ignore invalid dates
            }
        }

        $age = (int) $player->date_of_birth->diffInYears($currentDate);
        $marketValueCents = Money::parseMarketValue($playerData['marketValue'] ?? null);
        $annualWage = $contractService->calculateAnnualWage($marketValueCents, $minimumWage, $age);

        $currentAbility = (int) round(
            ($player->technical_ability + $player->physical_ability) / 2
        );
        $potentialData = $developmentService->generatePotential(
            $age,
            $currentAbility
        );

        return [
            'id' => Str::uuid()->toString(),
            'game_id' => $this->gameId,
            'player_id' => $player->id,
            'team_id' => $team->id,
            'number' => isset($playerData['number']) ? (int) $playerData['number'] : null,
            'position' => $playerData['position'] ?? 'Unknown',
            'market_value' => $playerData['marketValue'] ?? null,
            'market_value_cents' => $marketValueCents,
            'contract_until' => $contractUntil,
            'annual_wage' => $annualWage,
            'fitness' => rand(90, 100),
            'morale' => rand(65, 80),
            'durability' => InjuryService::generateDurability(),
            'game_technical_ability' => $player->technical_ability,
            'game_physical_ability' => $player->physical_ability,
            'potential' => $potentialData['potential'],
            'potential_low' => $potentialData['low'],
            'potential_high' => $potentialData['high'],
            'season_appearances' => 0,
        ];
    }

    private function resolveReferencePlayer(array $playerData, Collection $allPlayers, Carbon $currentDate): ?Player
    {
        $externalId = ExternalData::playerExternalId($playerData);
        if (!$externalId) {
            return null;
        }

        $player = $allPlayers->get($externalId);
        if ($player) {
            return $player;
        }

        $dateOfBirth = null;
        if (!empty($playerData['dateOfBirth'])) {
            try {
                $dateOfBirth = Carbon::parse($playerData['dateOfBirth'])->toDateString();
            } catch (\Throwable) {
                $dateOfBirth = null;
            }
        }

        $age = $dateOfBirth
            ? Carbon::parse($dateOfBirth)->diffInYears($currentDate)
            : 25;

        $foot = match (strtolower((string) ($playerData['foot'] ?? ''))) {
            'left' => 'left',
            'right' => 'right',
            'both' => 'both',
            default => null,
        };

        $marketValueCents = Money::parseMarketValue($playerData['marketValue'] ?? null);
        [$technical, $physical] = app(PlayerValuationService::class)->marketValueToAbilities(
            $marketValueCents,
            $playerData['position'] ?? 'Central Midfield',
            $age
        );

        $player = Player::create([
            'external_source' => ExternalData::defaultSource(),
            'external_id' => $externalId,
            'name' => $playerData['name'] ?? $externalId,
            'date_of_birth' => $dateOfBirth,
            'nationality' => $playerData['nationality'] ?? [],
            'height' => $playerData['height'] ?? null,
            'foot' => $foot,
            'technical_ability' => $technical,
            'physical_ability' => $physical,
        ]);

        // Backfill legacy column when it exists in older user databases.
        DB::table('players')
            ->where('id', $player->id)
            ->update(['transfermarkt_id' => $externalId]);

        $allPlayers->put($externalId, $player);

        return $player;
    }

    private function loadClubsFromTeamsJson(string $teamsFilePath): array
    {
        $data = ExternalData::decodeJsonFile($teamsFilePath);
        return $data['clubs'] ?? [];
    }

    private function loadClubsFromTeamPoolFiles(string $basePath): array
    {
        $clubs = [];

        foreach (glob("{$basePath}/*.json") as $filePath) {
            try {
                $data = ExternalData::decodeJsonFile($filePath);
            } catch (\RuntimeException) {
                continue;
            }

            $clubs[] = [
                'image' => $data['image'] ?? '',
                'externalId' => ExternalData::extractIdFromImage($data['image'] ?? ''),
                'players' => $data['players'] ?? [],
            ];
        }

        return $clubs;
    }
}
