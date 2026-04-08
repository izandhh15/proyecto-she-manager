<?php

namespace App\Modules\Season\Processors;

use App\Modules\Season\Contracts\SeasonProcessor;
use App\Modules\Season\DTOs\SeasonTransitionData;
use App\Modules\Match\Services\MatchVenueService;
use App\Models\ClubProfile;
use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Generates pre-season fixtures for career mode games.
 * Creates 4 friendlies against foreign teams of similar reputation,
 * scheduled every ~10 days from mid-July to mid-August.
 *
 * Priority: 108 (after ContinentalAndCupInitProcessor at 106)
 */
class PreSeasonFixtureProcessor implements SeasonProcessor
{
    public function __construct(
        private readonly MatchVenueService $venueService,
    ) {}

    public function priority(): int
    {
        return 108;
    }

    public function process(Game $game, SeasonTransitionData $data): SeasonTransitionData
    {
        if (! $game->isCareerMode()) {
            return $data;
        }

        $seasonYear = (int) $data->newSeason;
        $opponents = $this->selectOpponents($game);
        $schedule = config('preseason.schedule', []);
        $competitionId = config('preseason.competition_id', 'PRESEASON');

        foreach ($schedule as $i => $slot) {
            if (! isset($opponents[$i])) {
                break;
            }

            $date = Carbon::createFromDate($seasonYear, $slot['month'], $slot['day']);
            $homeTeamId = $slot['home'] ? $game->team_id : $opponents[$i]->id;
            $awayTeamId = $slot['home'] ? $opponents[$i]->id : $game->team_id;
            $venue = $this->venueService->resolve(
                homeTeamId: $homeTeamId,
                competitionId: $competitionId,
                roundNumber: $i + 1,
                matchKey: implode('|', [$game->id, $homeTeamId, $awayTeamId, $date->toDateString()]),
            );

            GameMatch::create([
                'id' => Str::uuid()->toString(),
                'game_id' => $game->id,
                'competition_id' => $competitionId,
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
                'scheduled_date' => $date->toDateString(),
                'venue_name' => $venue['venue_name'],
                'venue_capacity' => $venue['venue_capacity'],
                'round_number' => $i + 1,
                'played' => false,
            ]);
        }

        return $data;
    }

    /**
     * Select foreign teams of similar reputation as pre-season opponents.
     *
     * @return \Illuminate\Support\Collection<Team>
     */
    private function selectOpponents(Game $game): \Illuminate\Support\Collection
    {
        $userProfile = ClubProfile::where('team_id', $game->team_id)->first();
        $userTierIndex = $userProfile
            ? ClubProfile::getReputationTierIndex($userProfile->reputation_level)
            : 3;

        // Get reputation levels within Â±1 tier
        $tiers = ClubProfile::REPUTATION_TIERS;
        $validLevels = [];
        for ($i = max(0, $userTierIndex - 1); $i <= min(count($tiers) - 1, $userTierIndex + 1); $i++) {
            $validLevels[] = $tiers[$i];
        }

        $userCountry = $game->country ?? 'ES';

        // Find foreign teams with matching reputation
        return Team::where('country', '!=', $userCountry)
            ->whereHas('clubProfile', function ($query) use ($validLevels) {
                $query->whereIn('reputation_level', $validLevels);
            })
            ->inRandomOrder()
            ->limit((int) config('preseason.num_matches', 4))
            ->get();
    }
}
