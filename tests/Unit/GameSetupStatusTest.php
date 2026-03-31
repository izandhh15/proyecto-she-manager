<?php

namespace Tests\Unit;

use App\Http\Views\GameSetupStatus;
use App\Models\Game;
use App\Modules\Season\Jobs\SetupNewGame;
use App\Modules\Season\Jobs\SetupTournamentGame;
use Illuminate\Support\Facades\Queue;
use ReflectionMethod;
use Tests\TestCase;

class GameSetupStatusTest extends TestCase
{
    public function test_dispatch_setup_recovery_uses_tournament_job_for_tournament_games(): void
    {
        Queue::fake();

        $game = new Game([
            'id' => 'game-tournament',
            'team_id' => 'team-1',
            'competition_id' => 'WC2026',
            'season' => '2025',
            'game_mode' => Game::MODE_TOURNAMENT,
        ]);

        $method = new ReflectionMethod(GameSetupStatus::class, 'dispatchSetupRecovery');
        $method->setAccessible(true);
        $method->invoke(new GameSetupStatus(), $game);

        Queue::assertPushed(SetupTournamentGame::class, fn (SetupTournamentGame $job) => $job->gameId === 'game-tournament');
        Queue::assertNotPushed(SetupNewGame::class);
    }

    public function test_dispatch_setup_recovery_uses_career_job_for_career_games(): void
    {
        Queue::fake();

        $game = new Game([
            'id' => 'game-career',
            'team_id' => 'team-2',
            'competition_id' => 'ESP1',
            'season' => '2025',
            'game_mode' => Game::MODE_CAREER,
        ]);

        $method = new ReflectionMethod(GameSetupStatus::class, 'dispatchSetupRecovery');
        $method->setAccessible(true);
        $method->invoke(new GameSetupStatus(), $game);

        Queue::assertPushed(SetupNewGame::class, fn (SetupNewGame $job) => $job->gameId === 'game-career');
        Queue::assertNotPushed(SetupTournamentGame::class);
    }
}
