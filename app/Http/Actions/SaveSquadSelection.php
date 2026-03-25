<?php

namespace App\Http\Actions;

use App\Modules\Season\Services\TournamentRosterFallbackService;
use App\Modules\Player\Services\InjuryService;
use App\Modules\Player\Services\PlayerDevelopmentService;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Player;
use App\Support\ExternalData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SaveSquadSelection
{
    public function __construct(
        private readonly PlayerDevelopmentService $developmentService,
    ) {}

    public function __invoke(Request $request, string $gameId)
    {
        $game = Game::with('team')->findOrFail($gameId);

        if (!$game->isTournamentMode() || !$game->needsNewSeasonSetup()) {
            return redirect()->route('show-game', $gameId);
        }

        $request->validate([
            'player_ids' => 'required|array|max:26',
            'player_ids.*' => 'required|string',
        ]);

        $selectedExternalIds = $request->input('player_ids');

        // Load and validate against JSON candidates
        $externalId = $game->team->external_id;
        $jsonPath = base_path("data/2025/WC2026/teams/{$externalId}.json");
        $data = json_decode(file_get_contents($jsonPath), true);
        $jsonPlayers = collect($data['players'] ?? []);
        $validExternalIds = $jsonPlayers
            ->map(fn (array $player) => ExternalData::playerExternalId($player))
            ->filter()
            ->values()
            ->toArray();

        // Verify all selected IDs are valid candidates
        $invalidIds = array_diff($selectedExternalIds, $validExternalIds);
        if (!empty($invalidIds)) {
            return back()->with('error', __('squad.invalid_selection'));
        }

        // Build position lookup from JSON
        $positionByExternalId = $jsonPlayers
            ->mapWithKeys(fn (array $player) => [ExternalData::playerExternalId($player) => $player['position'] ?? 'Central Midfield'])
            ->filter(fn ($position, $id) => $id !== null)
            ->toArray();

        self::createTournamentGamePlayers($gameId, $game->team_id, $selectedExternalIds, $positionByExternalId);

        $game->completeNewSeasonSetup();

        return redirect()->route('show-game', $gameId)
            ->with('success', __('squad.squad_confirmed'));
    }

    public static function createTournamentGamePlayers(string $gameId, string $teamId, array $externalIds, array $positionByExternalId): void
    {
        $game = Game::with('team')->findOrFail($gameId);
        $playerModels = Player::whereIn('external_id', $externalIds)->get()->keyBy('external_id');

        $playerRows = [];
        foreach ($externalIds as $externalId) {
            $player = $playerModels->get($externalId);
            if (!$player) {
                continue;
            }

            $playerRows[] = [
                'id' => Str::uuid()->toString(),
                'game_id' => $gameId,
                'player_id' => $player->id,
                'team_id' => $teamId,
                'number' => null,
                'position' => $positionByExternalId[$externalId] ?? 'Central Midfield',
                'market_value' => null,
                'market_value_cents' => 0,
                'contract_until' => null,
                'annual_wage' => 0,
                'fitness' => rand(90, 100),
                'morale' => rand(70, 85),
                'durability' => InjuryService::generateDurability(),
                'game_technical_ability' => $player->technical_ability,
                'game_physical_ability' => $player->physical_ability,
                'season_appearances' => 0,
            ];
        }

        if (! empty($playerRows)) {
            GamePlayer::insert($playerRows);
        }

        app(TournamentRosterFallbackService::class)->ensureMinimumSquad($game, $game->team);
    }
}
