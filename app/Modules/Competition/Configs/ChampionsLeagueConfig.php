<?php

namespace App\Modules\Competition\Configs;

use App\Modules\Competition\Contracts\CompetitionConfig;

class ChampionsLeagueConfig implements CompetitionConfig
{
    /**
     * UCL knockout round prize money (in cents).
     */
    private const KNOCKOUT_PRIZE_MONEY = [
        1 => 100_000_000,      // â‚¬1M - Knockout Playoff
        2 => 200_000_000,      // â‚¬2M - Round of 16
        3 => 350_000_000,      // â‚¬3.5M - Quarter-finals
        4 => 500_000_000,      // â‚¬5M - Semi-finals
        5 => 1_000_000_000,    // â‚¬10M - Final (winner)
    ];

    /**
     * UCL prize money by league phase position (in cents).
     * Based on UEFA coefficient + performance payments.
     */
    private const TV_REVENUE = [
        1 => 8_000_000_000,    // â‚¬80M
        2 => 7_500_000_000,    // â‚¬75M
        3 => 7_000_000_000,    // â‚¬70M
        4 => 6_500_000_000,    // â‚¬65M
        5 => 6_000_000_000,    // â‚¬60M
        6 => 5_500_000_000,    // â‚¬55M
        7 => 5_000_000_000,    // â‚¬50M
        8 => 4_800_000_000,    // â‚¬48M (direct R16)
        9 => 4_500_000_000,    // â‚¬45M
        10 => 4_300_000_000,   // â‚¬43M
        11 => 4_100_000_000,   // â‚¬41M
        12 => 3_900_000_000,   // â‚¬39M
        13 => 3_700_000_000,   // â‚¬37M
        14 => 3_500_000_000,   // â‚¬35M
        15 => 3_300_000_000,   // â‚¬33M
        16 => 3_100_000_000,   // â‚¬31M
        17 => 2_900_000_000,   // â‚¬29M
        18 => 2_800_000_000,   // â‚¬28M
        19 => 2_700_000_000,   // â‚¬27M
        20 => 2_600_000_000,   // â‚¬26M
        21 => 2_500_000_000,   // â‚¬25M
        22 => 2_400_000_000,   // â‚¬24M
        23 => 2_300_000_000,   // â‚¬23M
        24 => 2_200_000_000,   // â‚¬22M (last playoff spot)
        25 => 2_000_000_000,   // â‚¬20M (eliminated)
        26 => 1_900_000_000,   // â‚¬19M
        27 => 1_800_000_000,   // â‚¬18M
        28 => 1_700_000_000,   // â‚¬17M
        29 => 1_600_000_000,   // â‚¬16M
        30 => 1_500_000_000,   // â‚¬15M
        31 => 1_400_000_000,   // â‚¬14M
        32 => 1_300_000_000,   // â‚¬13M
        33 => 1_200_000_000,   // â‚¬12M
        34 => 1_100_000_000,   // â‚¬11M
        35 => 1_000_000_000,   // â‚¬10M
        36 => 900_000_000,     // â‚¬9M
    ];

    public function getTvRevenue(int $position): int
    {
        return self::TV_REVENUE[$position] ?? self::TV_REVENUE[36];
    }

    public function getPositionFactor(int $position): float
    {
        if ($position <= 8) {
            return 1.15;
        }
        if ($position <= 24) {
            return 1.05;
        }

        return 0.95;
    }

    public function getTopScorerAwardName(): string
    {
        return 'season.top_scorer';
    }

    public function getBestGoalkeeperAwardName(): string
    {
        return 'season.best_goalkeeper';
    }

    public function getKnockoutPrizeMoney(int $roundNumber): int
    {
        return self::KNOCKOUT_PRIZE_MONEY[$roundNumber] ?? 0;
    }

    public function getStandingsZones(): array
    {
        return [
            [
                'minPosition' => 1,
                'maxPosition' => 8,
                'borderColor' => 'blue-500',
                'bgColor' => 'bg-blue-500',
                'label' => 'game.ucl_direct_knockout',
            ],
            [
                'minPosition' => 9,
                'maxPosition' => 24,
                'borderColor' => 'yellow-500',
                'bgColor' => 'bg-yellow-500',
                'label' => 'game.ucl_knockout_playoff',
            ],
            [
                'minPosition' => 25,
                'maxPosition' => 36,
                'borderColor' => 'red-500',
                'bgColor' => 'bg-red-500',
                'label' => 'game.ucl_eliminated',
            ],
        ];
    }

}
