<?php

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\CompetitionEntry;
use App\Models\Game;
use App\Models\GameStanding;
use App\Models\SimulatedSeason;
use App\Models\Team;
use App\Models\User;
use App\Modules\Competition\Playoffs\ESP2PlayoffGenerator;
use App\Modules\Competition\Promotions\ConfigDrivenPromotionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionRelegationValidationTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;
    private Competition $topDivision;
    private Competition $bottomDivision;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->topDivision = Competition::factory()->league()->create(['id' => 'ESP1', 'tier' => 1]);
        $this->bottomDivision = Competition::factory()->league()->create(['id' => 'ESP2', 'tier' => 2]);

        $team = Team::factory()->create();
        $this->game = Game::factory()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'competition_id' => 'ESP1',
            'season' => '2025',
        ]);
    }

    private function createStandings(string $competitionId, int $count): array
    {
        $teams = [];
        for ($i = 1; $i <= $count; $i++) {
            $team = Team::factory()->create();
            $teams[] = $team;

            GameStanding::create([
                'game_id' => $this->game->id,
                'competition_id' => $competitionId,
                'team_id' => $team->id,
                'position' => $i,
                'played' => 38,
                'won' => 20 - $i,
                'drawn' => 5,
                'lost' => $i,
                'goals_for' => 50 - $i,
                'goals_against' => 20 + $i,
                'points' => (20 - $i) * 3 + 5,
            ]);
        }

        return $teams;
    }

    // ──────────────────────────────────────────────────
    // getPromotedTeams validation
    // ──────────────────────────────────────────────────

    public function test_promoted_teams_from_real_standings_returns_expected_count(): void
    {
        $this->createStandings('ESP2', 22);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [18, 19, 20],
            directPromotionPositions: [1, 2],
            playoffGenerator: null,
        );

        // Without playoff generator, only direct promotion positions are used
        // But expected count = count(relegatedPositions) = 3, and we only get 2
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('expected 3 promoted teams');

        $rule->getPromotedTeams($this->game);
    }

    public function test_promoted_teams_with_playoff_fallback_returns_3(): void
    {
        $this->createStandings('ESP2', 22);

        // No playoff generator, but relegatedPositions matches directPromotionPositions count
        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [1, 2],
            directPromotionPositions: [1, 2],
            playoffGenerator: null,
        );

        $promoted = $rule->getPromotedTeams($this->game);
        $this->assertCount(2, $promoted);
    }

    public function test_promoted_teams_from_simulated_throws_when_count_wrong(): void
    {
        // Create simulated season with only 2 results instead of 3
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        SimulatedSeason::create([
            'game_id' => $this->game->id,
            'season' => '2025',
            'competition_id' => 'ESP2',
            'results' => [$team1->id, $team2->id],
        ]);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [18, 19, 20],
            directPromotionPositions: [1, 2],
            playoffGenerator: null,
        );

        // No real standings, falls back to simulated. Expects 3 (count of relegatedPositions)
        // but simulated only has 2 results
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('expected 3 promoted teams');

        $rule->getPromotedTeams($this->game);
    }

    // ──────────────────────────────────────────────────
    // getRelegatedTeams validation
    // ──────────────────────────────────────────────────

    public function test_direct_promotion_skips_blocked_reserve_team(): void
    {
        $parent = Team::factory()->create();
        $reserve = Team::factory()->create(['parent_team_id' => $parent->id]);
        $eligible = Team::factory()->create();

        CompetitionEntry::create([
            'game_id' => $this->game->id,
            'competition_id' => 'ESP1',
            'team_id' => $parent->id,
            'entry_round' => 1,
        ]);

        $this->createStanding('ESP2', $reserve->id, 1);
        $this->createStanding('ESP2', $eligible->id, 2);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [1],
            directPromotionPositions: [1],
        );

        $promoted = $rule->getPromotedTeams($this->game);

        $this->assertCount(1, $promoted);
        $this->assertSame($eligible->id, $promoted[0]['teamId']);
        $this->assertSame(2, $promoted[0]['position']);
    }

    public function test_playoff_generator_skips_blocked_reserve_team_and_slides_next_team_in(): void
    {
        $parent = Team::factory()->create();
        $directPromotionTeam = Team::factory()->create();
        $reserve = Team::factory()->create(['parent_team_id' => $parent->id]);
        $third = Team::factory()->create();
        $fourth = Team::factory()->create();
        $fifth = Team::factory()->create();
        $sixth = Team::factory()->create();

        CompetitionEntry::create([
            'game_id' => $this->game->id,
            'competition_id' => 'ESP1',
            'team_id' => $parent->id,
            'entry_round' => 1,
        ]);

        $this->createStanding('ESP2', $directPromotionTeam->id, 1);
        $this->createStanding('ESP2', $reserve->id, 2);
        $this->createStanding('ESP2', $third->id, 3);
        $this->createStanding('ESP2', $fourth->id, 4);
        $this->createStanding('ESP2', $fifth->id, 5);
        $this->createStanding('ESP2', $sixth->id, 6);

        $generator = new ESP2PlayoffGenerator(
            competitionId: 'ESP2',
            qualifyingPositions: [2, 3, 4, 5],
            directPromotionPositions: [1],
            triggerMatchday: 26,
        );

        $matchups = $generator->generateMatchups($this->game, 1);

        $this->assertSame([
            [$sixth->id, $third->id],
            [$fifth->id, $fourth->id],
        ], $matchups);
    }

    public function test_relegated_teams_returns_expected_count(): void
    {
        $this->createStandings('ESP1', 20);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [18, 19, 20],
            directPromotionPositions: [1, 2],
        );

        $relegated = $rule->getRelegatedTeams($this->game);
        $this->assertCount(3, $relegated);
    }

    public function test_relegated_teams_throws_when_standings_incomplete(): void
    {
        // Only create 17 teams — positions 18, 19, 20 don't exist
        $this->createStandings('ESP1', 17);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [18, 19, 20],
            directPromotionPositions: [1, 2],
        );

        // Real standings exist but positions 18-20 are missing
        // Falls to simulated path, which also has nothing → 0 teams
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('expected 3 relegated teams');

        $rule->getRelegatedTeams($this->game);
    }

    public function test_relegated_teams_from_simulated_returns_expected_count(): void
    {
        $teams = [];
        for ($i = 0; $i < 20; $i++) {
            $teams[] = Team::factory()->create()->id;
        }

        SimulatedSeason::create([
            'game_id' => $this->game->id,
            'season' => '2025',
            'competition_id' => 'ESP1',
            'results' => $teams,
        ]);

        $rule = new ConfigDrivenPromotionRule(
            topDivision: 'ESP1',
            bottomDivision: 'ESP2',
            relegatedPositions: [18, 19, 20],
            directPromotionPositions: [1, 2],
        );

        $relegated = $rule->getRelegatedTeams($this->game);
        $this->assertCount(3, $relegated);
        $this->assertEquals($teams[17], $relegated[0]['teamId']);
        $this->assertEquals($teams[18], $relegated[1]['teamId']);
        $this->assertEquals($teams[19], $relegated[2]['teamId']);
    }

    private function createStanding(string $competitionId, string $teamId, int $position): void
    {
        GameStanding::create([
            'game_id' => $this->game->id,
            'competition_id' => $competitionId,
            'team_id' => $teamId,
            'position' => $position,
            'played' => 26,
            'won' => max(0, 20 - $position),
            'drawn' => 5,
            'lost' => $position,
            'goals_for' => max(0, 50 - $position),
            'goals_against' => 20 + $position,
            'points' => (max(0, 20 - $position) * 3) + 5,
        ]);
    }
}
