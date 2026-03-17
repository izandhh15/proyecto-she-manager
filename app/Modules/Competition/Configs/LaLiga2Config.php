<?php

namespace App\Modules\Competition\Configs;

use App\Modules\Competition\Contracts\CompetitionConfig;
use App\Modules\Competition\Contracts\HasSeasonGoals;
use App\Models\ClubProfile;
use App\Models\Game;

class LaLiga2Config implements CompetitionConfig, HasSeasonGoals
{
    /**
     * La Liga 2 TV revenue by position (in cents).
     */
    private const TV_REVENUE = [
        1 => 900_000_000,      // EUR9M
        2 => 850_000_000,      // EUR8.5M
        3 => 800_000_000,      // EUR8M
        4 => 750_000_000,      // EUR7.5M
        5 => 700_000_000,      // EUR7M
        6 => 700_000_000,      // EUR7M
        7 => 650_000_000,      // EUR6.5M
        8 => 650_000_000,      // EUR6.5M
        9 => 650_000_000,      // EUR6.5M
        10 => 600_000_000,     // EUR6M
        11 => 600_000_000,     // EUR6M
        12 => 600_000_000,     // EUR6M
        13 => 600_000_000,     // EUR6M
        14 => 600_000_000,     // EUR6M
        15 => 550_000_000,     // EUR5.5M
        16 => 550_000_000,     // EUR5.5M
        17 => 550_000_000,     // EUR5.5M
        18 => 550_000_000,     // EUR5.5M
        19 => 500_000_000,     // EUR5M
        20 => 500_000_000,     // EUR5M
        21 => 500_000_000,     // EUR5M
        22 => 500_000_000,     // EUR5M
    ];

    private const POSITION_FACTORS = [
        'top' => 1.05,
        'mid_high' => 1.0,
        'mid_low' => 0.95,
        'relegation' => 0.85,
    ];

    /**
     * Map reputation to season goal.
     */
    private const REPUTATION_TO_GOAL = [
        ClubProfile::REPUTATION_ELITE => Game::GOAL_PROMOTION,
        ClubProfile::REPUTATION_CONTINENTAL => Game::GOAL_PROMOTION,
        ClubProfile::REPUTATION_ESTABLISHED => Game::GOAL_PLAYOFF,
        ClubProfile::REPUTATION_MODEST => Game::GOAL_TOP_HALF,
        ClubProfile::REPUTATION_LOCAL => Game::GOAL_SURVIVAL,
    ];

    public function getTvRevenue(int $position): int
    {
        $lastConfiguredPosition = max(array_keys(self::TV_REVENUE));

        return self::TV_REVENUE[$position] ?? self::TV_REVENUE[$lastConfiguredPosition];
    }

    public function getPositionFactor(int $position): float
    {
        $promotionZoneEnd = max(array_merge($this->directPromotionPositions(), $this->playoffPositions()));
        $relegationStart = min($this->relegationPositions());
        $midSpan = max(0, ($relegationStart - 1) - $promotionZoneEnd);
        $midHighEnd = $promotionZoneEnd + (int) ceil($midSpan / 2);

        if ($position <= $promotionZoneEnd) {
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
        $promotionTarget = max($this->directPromotionPositions());
        $playoffTarget = max($this->playoffPositions() ?: [$promotionTarget + 1]);

        return match ($goal) {
            Game::GOAL_PROMOTION => $promotionTarget,
            Game::GOAL_PLAYOFF => $playoffTarget,
            Game::GOAL_TOP_HALF => $topHalf,
            Game::GOAL_SURVIVAL => $survival,
            default => $topHalf,
        };
    }

    public function getAvailableGoals(): array
    {
        return [
            Game::GOAL_PROMOTION => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_PROMOTION), 'label' => 'game.goal_promotion'],
            Game::GOAL_PLAYOFF => ['targetPosition' => $this->getGoalTargetPosition(Game::GOAL_PLAYOFF), 'label' => 'game.goal_playoff'],
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
        $zones = [];

        if (!empty($this->directPromotionPositions())) {
            $zones[] = [
                'minPosition' => min($this->directPromotionPositions()),
                'maxPosition' => max($this->directPromotionPositions()),
                'borderColor' => 'green-500',
                'bgColor' => 'bg-green-500',
                'label' => 'game.direct_promotion',
            ];
        }

        if (!empty($this->playoffPositions())) {
            $zones[] = [
                'minPosition' => min($this->playoffPositions()),
                'maxPosition' => max($this->playoffPositions()),
                'borderColor' => 'green-300',
                'bgColor' => 'bg-green-300',
                'label' => 'game.promotion_playoff',
            ];
        }

        $zones[] = [
            'minPosition' => min($this->relegationPositions()),
            'maxPosition' => max($this->relegationPositions()),
            'borderColor' => 'red-500',
            'bgColor' => 'bg-red-500',
            'label' => 'game.relegation',
        ];

        return $zones;
    }

    private function teamCount(): int
    {
        return (int) config('countries.ES.tiers.2.teams', 22);
    }

    /**
     * @return array{top_division: string, bottom_division: string, relegated_positions: int[], direct_promotion_positions: int[], playoff_positions?: int[]}|null
     */
    private function promotionRule(): ?array
    {
        $promotions = config('countries.ES.promotions', []);

        return collect($promotions)->first(fn ($promotion) => $promotion['bottom_division'] === 'ESP2');
    }

    /**
     * @return int[]
     */
    private function directPromotionPositions(): array
    {
        $rule = $this->promotionRule();

        return $rule['direct_promotion_positions'] ?? [1, 2];
    }

    /**
     * @return int[]
     */
    private function playoffPositions(): array
    {
        $rule = $this->promotionRule();

        return $rule['playoff_positions'] ?? [];
    }

    /**
     * @return int[]
     */
    private function relegationPositions(): array
    {
        $rule = $this->promotionRule();

        return $rule['relegated_positions'] ?? [$this->teamCount() - 1, $this->teamCount()];
    }
}
