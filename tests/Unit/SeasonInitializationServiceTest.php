<?php

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\CompetitionEntry;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use App\Modules\Season\Services\SeasonInitializationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonInitializationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_conduct_cup_draws_preserves_staged_cup_entries(): void
    {
        Competition::factory()->league()->create([
            'id' => 'ESP1',
            'name' => 'Liga F',
            'season' => '2025',
        ]);

        Competition::factory()->knockoutCup()->create([
            'id' => 'ESPCUP',
            'name' => 'Copa de la Reina',
            'season' => '2025',
        ]);

        Competition::factory()->knockoutCup()->create([
            'id' => 'ESPSUP',
            'name' => 'Supercopa de España',
            'season' => '2025',
        ]);

        $user = User::factory()->create();
        $managedTeam = Team::factory()->create();

        $game = Game::factory()->create([
            'user_id' => $user->id,
            'team_id' => $managedTeam->id,
            'competition_id' => 'ESP1',
            'season' => '2025',
            'country' => 'ES',
        ]);

        $roundOneA = Team::factory()->create();
        $roundOneB = Team::factory()->create();
        $thirdRoundTeam = Team::factory()->create();
        $octavosTeam = Team::factory()->create();
        $supercupTeams = Team::factory()->count(4)->create();

        CompetitionEntry::create([
            'game_id' => $game->id,
            'competition_id' => 'ESPCUP',
            'team_id' => $roundOneA->id,
            'entry_round' => 1,
        ]);

        CompetitionEntry::create([
            'game_id' => $game->id,
            'competition_id' => 'ESPCUP',
            'team_id' => $roundOneB->id,
            'entry_round' => 1,
        ]);

        CompetitionEntry::create([
            'game_id' => $game->id,
            'competition_id' => 'ESPCUP',
            'team_id' => $thirdRoundTeam->id,
            'entry_round' => 3,
        ]);

        CompetitionEntry::create([
            'game_id' => $game->id,
            'competition_id' => 'ESPCUP',
            'team_id' => $octavosTeam->id,
            'entry_round' => 4,
        ]);

        foreach ($supercupTeams as $index => $team) {
            CompetitionEntry::create([
                'game_id' => $game->id,
                'competition_id' => 'ESPCUP',
                'team_id' => $team->id,
                'entry_round' => $index === 0 ? 1 : 4,
            ]);

            CompetitionEntry::create([
                'game_id' => $game->id,
                'competition_id' => 'ESPSUP',
                'team_id' => $team->id,
                'entry_round' => 1,
            ]);
        }

        app(SeasonInitializationService::class)->conductCupDraws($game->id, 'ES');

        $this->assertSame(
            3,
            CompetitionEntry::where('game_id', $game->id)
                ->where('competition_id', 'ESPCUP')
                ->where('team_id', $thirdRoundTeam->id)
                ->value('entry_round')
        );

        $this->assertSame(
            4,
            CompetitionEntry::where('game_id', $game->id)
                ->where('competition_id', 'ESPCUP')
                ->where('team_id', $octavosTeam->id)
                ->value('entry_round')
        );

        $this->assertSame(
            4,
            CompetitionEntry::where('game_id', $game->id)
                ->where('competition_id', 'ESPCUP')
                ->where('team_id', $supercupTeams[0]->id)
                ->value('entry_round')
        );
    }
}
