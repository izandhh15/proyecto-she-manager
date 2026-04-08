<?php

namespace App\Modules\Match\Services;

use App\Models\Competition;
use App\Models\Game;
use App\Models\GameMatch;
use App\Models\GameStanding;
use App\Models\Team;
use App\Models\TeamReputation;
use Illuminate\Support\Collection;

class MatchAttendanceService
{
    private const EXPECTED_BASELINE_OCCUPANCY = 0.60;

    /**
     * @param  Collection<int, GameMatch>  $matches
     * @param  Collection<string, Competition>  $competitions
     * @return array<string, int>
     */
    public function calculateForMatches(Game $game, Collection $matches, Collection $competitions): array
    {
        if ($matches->isEmpty()) {
            return [];
        }

        $homeIds = $matches->pluck('home_team_id')->unique()->values()->all();
        $teamIds = $matches->pluck('home_team_id')
            ->merge($matches->pluck('away_team_id'))
            ->unique()
            ->values()
            ->all();

        $teams = Team::whereIn('id', $teamIds)->get()->keyBy('id');

        $standingLookup = GameStanding::where('game_id', $game->id)
            ->whereIn('competition_id', $matches->pluck('competition_id')->unique())
            ->whereIn('team_id', $teamIds)
            ->get()
            ->keyBy(fn (GameStanding $standing) => $standing->competition_id . '|' . $standing->team_id);

        $teamCountsByCompetition = GameStanding::where('game_id', $game->id)
            ->whereIn('competition_id', $matches->pluck('competition_id')->unique())
            ->selectRaw('competition_id, COUNT(*) as team_count')
            ->groupBy('competition_id')
            ->pluck('team_count', 'competition_id');

        $recentHomeMatches = GameMatch::where('game_id', $game->id)
            ->where('played', true)
            ->whereIn('home_team_id', $homeIds)
            ->orderByDesc('scheduled_date')
            ->get(['home_team_id', 'home_score', 'away_score'])
            ->groupBy('home_team_id');

        $attendanceByMatch = [];

        foreach ($matches as $match) {
            $homeTeam = $teams->get($match->home_team_id);
            $awayTeam = $teams->get($match->away_team_id);
            $competition = $competitions->get($match->competition_id);

            if (! $homeTeam || ! $awayTeam) {
                continue;
            }

            $capacity = (int) ($match->venue_capacity ?: $homeTeam->stadium_seats);

            if ($capacity <= 0) {
                continue;
            }

            $ratio = $this->attendanceRatio(
                game: $game,
                match: $match,
                homeTeam: $homeTeam,
                awayTeam: $awayTeam,
                competition: $competition,
                standingLookup: $standingLookup,
                teamCountsByCompetition: $teamCountsByCompetition,
                recentHomeMatches: $recentHomeMatches,
            );

            $attendanceByMatch[$match->id] = (int) round($capacity * $ratio);
        }

        return $attendanceByMatch;
    }

    public function expectedBaselineOccupancy(): float
    {
        return self::EXPECTED_BASELINE_OCCUPANCY;
    }

    /**
     * @param  Collection<string, GameStanding>  $standingLookup
     * @param  Collection<string, int>  $teamCountsByCompetition
     * @param  Collection<string, Collection<int, GameMatch>>  $recentHomeMatches
     */
    private function attendanceRatio(
        Game $game,
        GameMatch $match,
        Team $homeTeam,
        Team $awayTeam,
        ?Competition $competition,
        Collection $standingLookup,
        Collection $teamCountsByCompetition,
        Collection $recentHomeMatches,
    ): float {
        $base = match ($competition?->handler_type) {
            'preseason' => 0.36,
            'group_stage_cup' => 0.64,
            'swiss_format' => 0.68,
            default => $competition?->isCup() ? 0.60 : 0.55,
        };

        if (($homeTeam->getAttribute('type') ?? 'club') === 'national') {
            $base += 0.10;
        }

        $homeReputation = TeamReputation::resolveLevel($game->id, $homeTeam->id);
        $awayReputation = TeamReputation::resolveLevel($game->id, $awayTeam->id);

        $base += match ($homeReputation) {
            'elite' => 0.16,
            'continental' => 0.11,
            'established' => 0.06,
            'modest' => 0.00,
            default => -0.06,
        };

        $base += match ($awayReputation) {
            'elite' => 0.06,
            'continental' => 0.04,
            'established' => 0.02,
            'modest' => 0.00,
            default => -0.01,
        };

        $standingKey = $match->competition_id . '|' . $homeTeam->id;
        $standing = $standingLookup->get($standingKey);
        $teamCount = max(2, (int) ($teamCountsByCompetition->get($match->competition_id) ?? 20));

        if ($standing) {
            $positionStrength = 1 - (($standing->position - 1) / max(1, $teamCount - 1));
            $base += ($positionStrength - 0.5) * 0.18;
        }

        $recent = $recentHomeMatches->get($homeTeam->id, collect())->take(5);
        if ($recent->isNotEmpty()) {
            $points = $recent->sum(function (GameMatch $homeMatch) {
                if (($homeMatch->home_score ?? 0) > ($homeMatch->away_score ?? 0)) {
                    return 3;
                }

                if (($homeMatch->home_score ?? 0) === ($homeMatch->away_score ?? 0)) {
                    return 1;
                }

                return 0;
            });

            $formRatio = $points / max(1, $recent->count() * 3);
            $base += ($formRatio - 0.5) * 0.12;
        }

        if ($homeTeam->id === $game->team_id) {
            $facilitiesTier = $game->currentInvestment?->facilities_tier ?? 1;
            $base += (($facilitiesTier - 1) * 0.02);
        }

        $volatility = ((abs(crc32($match->id)) % 17) - 8) / 100;

        return min(0.98, max(0.18, $base + $volatility));
    }
}
