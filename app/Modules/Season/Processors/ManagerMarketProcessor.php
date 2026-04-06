<?php

namespace App\Modules\Season\Processors;

use App\Models\Competition;
use App\Models\CompetitionEntry;
use App\Models\Game;
use App\Models\GameStanding;
use App\Models\ManagerJobOffer;
use App\Models\Team;
use App\Modules\Season\Contracts\SeasonProcessor;
use App\Modules\Season\DTOs\SeasonTransitionData;
use Illuminate\Support\Facades\Schema;

class ManagerMarketProcessor implements SeasonProcessor
{
    public function process(Game $game, SeasonTransitionData $data): SeasonTransitionData
    {
        if (! Schema::hasTable('manager_job_offers')) {
            return $data;
        }

        $currentCompetition = Competition::find($game->competition_id);
        if (! $currentCompetition || $currentCompetition->role !== Competition::ROLE_LEAGUE) {
            return $data;
        }

        $standing = GameStanding::where('game_id', $game->id)
            ->where('competition_id', $game->competition_id)
            ->where('team_id', $game->team_id)
            ->first();

        $teamsInLeague = GameStanding::where('game_id', $game->id)
            ->where('competition_id', $game->competition_id)
            ->count();

        if (! $standing || $teamsInLeague === 0) {
            return $data;
        }

        $percentile = $standing->position / $teamsInLeague;
        $currentTier = max(1, (int) $currentCompetition->tier);

        $isSacked = $percentile >= 0.85;
        if (Schema::hasColumn('games', 'is_sacked')) {
            $game->update(['is_sacked' => $isSacked]);
        }

        // Clean pending offers for same season before generating new ones.
        ManagerJobOffer::where('game_id', $game->id)
            ->where('season', $data->oldSeason)
            ->where('status', ManagerJobOffer::STATUS_PENDING)
            ->update(['status' => ManagerJobOffer::STATUS_EXPIRED]);

        [$minTier, $maxTier] = $this->resolveTierRange($currentTier, $percentile, $isSacked);

        $candidateCompetitions = Competition::query()
            ->where('role', Competition::ROLE_LEAGUE)
            ->whereBetween('tier', [$minTier, $maxTier])
            ->where('id', '!=', $game->competition_id)
            ->where('country', $game->country)
            ->get();

        $createdClubOffers = 0;

        foreach ($candidateCompetitions as $competition) {
            $teamIds = CompetitionEntry::query()
                ->where('game_id', $game->id)
                ->where('competition_id', $competition->id)
                ->pluck('team_id');

            $offerTeam = Team::query()
                ->whereIn('id', $teamIds)
                ->where('id', '!=', $game->team_id)
                ->inRandomOrder()
                ->first();

            if (! $offerTeam) {
                continue;
            }

            ManagerJobOffer::create([
                'user_id' => $game->user_id,
                'game_id' => $game->id,
                'team_id' => $offerTeam->id,
                'competition_id' => $competition->id,
                'offer_type' => ManagerJobOffer::TYPE_CLUB,
                'status' => ManagerJobOffer::STATUS_PENDING,
                'season' => $data->oldSeason,
                'target_tier' => (int) $competition->tier,
                'priority' => $isSacked ? 3 : 1,
            ]);

            $createdClubOffers++;
            if ($createdClubOffers >= 3) {
                break;
            }
        }

        // National team offers are independent from club tier and optional.
        if (rand(1, 100) <= 35) {
            $nationalTeam = Team::query()
                ->where('type', 'national')
                ->where('id', '!=', $game->national_team_id)
                ->inRandomOrder()
                ->first();

            if ($nationalTeam) {
                ManagerJobOffer::create([
                    'user_id' => $game->user_id,
                    'game_id' => $game->id,
                    'team_id' => $nationalTeam->id,
                    'competition_id' => null,
                    'offer_type' => ManagerJobOffer::TYPE_NATIONAL,
                    'status' => ManagerJobOffer::STATUS_PENDING,
                    'season' => $data->oldSeason,
                    'target_tier' => null,
                    'priority' => 2,
                ]);
            }
        }

        return $data;
    }

    public function priority(): int
    {
        return 18;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveTierRange(int $currentTier, float $percentile, bool $isSacked): array
    {
        if ($isSacked) {
            return [$currentTier, $currentTier + 2];
        }

        if ($percentile <= 0.25) {
            return [max(1, $currentTier - 1), $currentTier];
        }

        if ($percentile <= 0.6) {
            return [$currentTier, $currentTier + 1];
        }

        return [$currentTier, $currentTier + 2];
    }
}
