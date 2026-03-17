<?php

namespace App\Modules\Competition\Configs;

use App\Modules\Competition\Contracts\CompetitionConfig;
use App\Modules\Competition\Contracts\HasSeasonGoals;
use App\Models\ClubProfile;
use App\Models\Game;

class LaLigaConfig implements CompetitionConfig, HasSeasonGoals
{
    /**
     * La Liga TV revenue by position (in cents).
     */
    private const TV_REVENUE = [
        1 => 15_500_000_000,   // EUR155M
        2 => 14_000_000_000,   // EUR140M
        3 => 10_500_000_000,   // EUR105M
        4 => 7_200_000_000,    // EUR72M
        5 => 6_800_000_000,    // EUR68M
        6 => 6_500_000_000,    // EUR65M
        7 => 6_200_000_000,    // EUR62M
        8 => 5_800_000_000,    // EUR58M
        9 => 5_500_000_000,    // EUR55M
        10 => 5_200_000_000,   // EUR52M
        11 => 4_800_000_000,   // EUR48M
        12 => 4_600_000_000,   // EUR46M
        13 => 4_500_000_000,   // EUR45M
        14 => 4_400_000_000,   // EUR44M
        15 => 4_300_000_000,   // EUR43M
        16 => 4_300_000_000,   // EUR43M
        17 => 4_200_000_000,   // EUR42M
        18 => 4_200_000_000,   // EUR42M
        19 => 4_100_000_000,   // EUR41M
        20 => 4_000_000_000,   // EUR40M
    ];

    private const POSITION_FACTORS = [
        'top' => 1.10,
        'mid_high' => 1.0,
        'mid_low' => 0.95,
        'relegation' => 0.85,
    ];

    /**
     * Map reputation to season goal.
     */
    private const REPUTATION_TO_GOAL = [
        ClubProfile::REPUTATION_ELITE => Game::GOAL_TITLE,
        ClubProfile::REPUTATION_CONTINENTAL => Game::GOAL_EUROPA_LEAGUE,
        ClubProfile::REPUTATION_ESTABLISHED => Game::GOAL_TOP_HALF,
        ClubProfile::REPUTATION_MODEST => Game::GOAL_SURVIVAL,
        ClubProfile::REPUTATION_LOCAL => Game::GOAL_SURVIVAL,
    ];

    public function getTvRevenue(int $position): int
    {
        $lastConfiguredPosition = max(array_keys(self::TV_REVENUE));

        return self::TV_REVENUE[$position] ?? self::TV_REVENUE[$lastConfiguredPosition];
    }

    public function getPositionFactor(int $position): float
    {
        $topZoneMax = max($this->europeanPositions() ?: [4]);
        $relegationStart = min($this->relegationPositions());
        $midSpan = max(0, ($relegationStart - 1) - $topZoneMax);
        $midHighEnd = $topZoneMax + (int) ceil($midSpan / 2);

        if ($position <= $topZoneMax) {
            return self::POSITION_FACTORS['top'];
        }
        if ($position <= $midHighEnd) {
            return self::POSITION_FACTORS['mid_high'];
        }
        if ($position < $relegationStart) {
            return self::POSITION_FACTORS['mid_low'];
        }

        return self::POSITION_FACTORS['relegation'];
    }

    public function getSeasonGoal(string $reputation): string
    {
        return self::REPUTATION_TO_GOAL[$reputation] ?? Game::GOAL_TOP_HALF;
    }

    public function getGoalTargetPosition(string $goal): int
    {
        $topHalf = max(1, (int) floor($this->teamCount() / 2));
        $survival = max(1, min($this->relegationPositions()) - 1);
        $europeanTarget = max($this->europeanGoalPositions() ?: [6]);

        return match ($goal) {
            Game::GOAL_TITLE => 1,
            Game::GOAL_EUROPA_LEAGUE => $europeanTarget,
            Game::GOAL_TOP_HALF => $topHalf,
            Game::GOAL_SURVIVAL => $survival,
            default => $topHalf,
        };
    }

    public function getAvailableGoals(): array
    {
        return [
            Game::GOAL_TITLE => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_TITLE), 'label' => 'game.goal_title'],
            Game::GOAL_EUROPA_LEAGUE => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_EUROPA_LEAGUE), 'label' => 'game.goal_europa_league'],
            Game::GOAL_TOP_HALF => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_TOP_HALF), 'label' => 'game.goal_top_half'],
            Game::GOAL_SURVIVAL => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_SURVIVAL), 'label' => 'game.goal_survival'],
        ];
    }

    public function getTopScorerAwardName(): string
    {
        return 'season.pichichi';
    }

    public function getBestGoalkeeperAwardName(): string
    {
        return 'season.zamora';
    }

    public function getKnockoutPrizeMoney(int $roundNumber): int
    {
        return 0;
    }

    public function getStandingsZones(): array
    {
        $slots = config('countries.ES.continental_slots.ESP1', []);
        $zones = [];

        if (!empty($slots['UCL'])) {
            $zones[] = [
                'minPosition' => min($slots['UCL']),
                'maxPosition' => max($slots['UCL']),
                'borderColor' => 'blue-500',
                'bgColor' => 'bg-blue-500',
                'label' => 'game.champions_league',
            ];
        }

        if (!empty($slots['UEL'])) {
            $zones[] = [
                'minPosition' => min($slots['UEL']),
                'maxPosition' => max($slots['UEL']),
                'borderColor' => 'orange-500',
                'bgColor' => 'bg-orange-500',
                'label' => 'game.europa_league',
            ];
        }

        if (!empty($slots['UECL'])) {
            $zones[] = [
                'minPosition' => min($slots['UECL']),
                'maxPosition' => max($slots['UECL']),
                'borderColor' => 'green-500',
                'bgColor' => 'bg-green-500',
                'label' => 'game.conference_league',
            ];
        }

        $relegation = $this->relegationPositions();
        if (!empty($relegation)) {
            $zones[] = [
                'minPosition' => min($relegation),
                'maxPosition' => max($relegation),
                'borderColor' => 'red-500',
                'bgColor' => 'bg-red-500',
                'label' => 'game.relegation',
            ];
        }

        return $zones;
    }

    private function teamCount(): int
    {
        return (int) config('countries.ES.tiers.1.teams', 20);
    }

    /**
     * @return int[]
     */
    private function relegationPositions(): array
    {
        $promotions = config('countries.ES.promotions', []);
        $rule = collect($promotions)->first(fn ($promotion) => $promotion['top_division'] === 'ESP1');

        return $rule['relegated_positions'] ?? [$this->teamCount() - 1, $this->teamCount()];
    }

    /**
     * @return int[]
     */
    private function europeanPositions(): array
    {
        return array_values(array_unique(array_merge(
            config('countries.ES.continental_slots.ESP1.UCL', []),
            config('countries.ES.continental_slots.ESP1.UEL', []),
            config('countries.ES.continental_slots.ESP1.UECL', []),
        )));
    }

    /**
     * @return int[]
     */
    private function europeanGoalPositions(): array
    {
        return config('countries.ES.continental_slots.ESP1.UEL', []) ?: $this->europeanPositions();
    }
}
