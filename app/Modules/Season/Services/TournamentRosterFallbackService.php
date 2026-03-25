<?php

namespace App\Modules\Season\Services;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Team;
use App\Modules\Squad\DTOs\GeneratedPlayerData;
use App\Modules\Squad\Services\PlayerGeneratorService;
use App\Support\PositionMapper;
use Carbon\Carbon;

class TournamentRosterFallbackService
{
    private const MINIMUM_SQUAD_SIZE = 23;

    private const TARGET_COUNTS = [
        'Goalkeeper' => 3,
        'Defender' => 7,
        'Midfielder' => 7,
        'Forward' => 6,
    ];

    private const POSITION_CYCLES = [
        'Goalkeeper' => ['Goalkeeper'],
        'Defender' => ['Centre-Back', 'Centre-Back', 'Left-Back', 'Right-Back'],
        'Midfielder' => ['Defensive Midfield', 'Central Midfield', 'Central Midfield', 'Attacking Midfield', 'Left Midfield', 'Right Midfield'],
        'Forward' => ['Centre-Forward', 'Centre-Forward', 'Left Winger', 'Right Winger', 'Second Striker'],
    ];

    public function __construct(
        private readonly PlayerGeneratorService $playerGeneratorService,
    ) {}

    public function ensureMinimumSquad(Game $game, Team $team): int
    {
        $existingPlayers = GamePlayer::where('game_id', $game->id)
            ->where('team_id', $team->id)
            ->get();

        $missing = self::MINIMUM_SQUAD_SIZE - $existingPlayers->count();
        if ($missing <= 0) {
            return 0;
        }

        $countsByGroup = [
            'Goalkeeper' => 0,
            'Defender' => 0,
            'Midfielder' => 0,
            'Forward' => 0,
        ];

        foreach ($existingPlayers as $player) {
            $group = PositionMapper::getPositionGroup($player->position ?? 'Central Midfield');
            $countsByGroup[$group] = ($countsByGroup[$group] ?? 0) + 1;
        }

        $positionQueue = [];
        foreach (self::TARGET_COUNTS as $group => $targetCount) {
            $groupMissing = max(0, $targetCount - ($countsByGroup[$group] ?? 0));
            if ($groupMissing === 0) {
                continue;
            }

            $cycle = self::POSITION_CYCLES[$group];
            for ($index = 0; $index < $groupMissing; $index++) {
                $positionQueue[] = $cycle[$index % count($cycle)];
            }
        }

        $currentDate = $game->current_date instanceof Carbon
            ? $game->current_date
            : Carbon::parse($game->current_date ?? now());

        $technicalBase = (int) round($existingPlayers->avg('game_technical_ability') ?? 56);
        $physicalBase = (int) round($existingPlayers->avg('game_physical_ability') ?? 56);
        $technicalBase = max(42, min(78, $technicalBase));
        $physicalBase = max(42, min(78, $physicalBase));

        $generated = 0;
        foreach (array_slice($positionQueue, 0, $missing) as $position) {
            $age = rand(18, 29);
            $dateOfBirth = (clone $currentDate)
                ->subYears($age)
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 27));

            $technical = max(40, min(85, $technicalBase + rand(-6, 6)));
            $physical = max(40, min(85, $physicalBase + rand(-6, 6)));

            $this->playerGeneratorService->create($game, new GeneratedPlayerData(
                teamId: $team->id,
                position: $position,
                technical: $technical,
                physical: $physical,
                dateOfBirth: $dateOfBirth,
                contractYears: 2,
                nationality: [$team->name],
                fitnessMin: 86,
                fitnessMax: 98,
                moraleMin: 68,
                moraleMax: 84,
            ));

            $generated++;
        }

        return $generated;
    }
}
