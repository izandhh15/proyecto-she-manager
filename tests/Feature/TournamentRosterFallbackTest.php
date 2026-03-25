<?php

namespace Tests\Feature;

use App\Http\Actions\SaveSquadSelection;
use App\Models\Competition;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentRosterFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_tournament_squad_selection_backfills_small_national_team_roster(): void
    {
        Competition::factory()->league()->create([
            'id' => 'WC2026',
            'role' => 'league',
            'tier' => 0,
        ]);

        $team = Team::factory()->create([
            'name' => 'Qatar',
            'country' => 'qa',
        ]);

        $game = Game::factory()->create([
            'user_id' => User::factory(),
            'team_id' => $team->id,
            'competition_id' => 'WC2026',
            'season' => '2025',
            'current_date' => '2025-06-10',
        ]);

        $player = Player::factory()->create([
            'external_id' => 'qat-1',
            'name' => 'Existing Qatar Player',
            'nationality' => ['Qatar'],
        ]);

        SaveSquadSelection::createTournamentGamePlayers(
            $game->id,
            $team->id,
            ['qat-1'],
            ['qat-1' => 'Goalkeeper'],
        );

        $gamePlayers = GamePlayer::where('game_id', $game->id)
            ->where('team_id', $team->id)
            ->get();

        $this->assertCount(23, $gamePlayers);
        $this->assertTrue($gamePlayers->contains('player_id', $player->id));
        $this->assertGreaterThanOrEqual(3, $gamePlayers->where('position', 'Goalkeeper')->count());
    }
}
