<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Game;
use App\Models\GameFinances;
use App\Models\GamePlayer;
use App\Models\Team;
use App\Models\User;
use App\Modules\Finance\Services\BudgetProjectionService;
use App\Modules\Player\Services\PlayerDevelopmentService;
use App\Modules\Season\Jobs\SetupNewGame;
use App\Modules\Season\Processors\LeagueFixtureProcessor;
use App\Modules\Season\Processors\StandingsResetProcessor;
use App\Modules\Season\Services\SeasonSetupPipeline;
use App\Modules\Transfer\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewSeasonRosterRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private string $testDataDir;
    private User $user;
    private Team $userTeam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDataDir = base_path('data/2025/TEST1');
        if (! is_dir($this->testDataDir)) {
            mkdir($this->testDataDir, 0777, true);
        }

        file_put_contents($this->testDataDir . '/teams.json', $this->bomEncodedJson([
            'id' => 'TEST1',
            'code' => 'test-league-2025',
            'name' => 'Test League',
            'seasonID' => '2025',
            'clubs' => $this->clubsPayload(),
        ]));

        file_put_contents($this->testDataDir . '/schedule.json', json_encode([
            'league' => [
                ['round' => 1, 'date' => '2025-08-10'],
                ['round' => 2, 'date' => '2025-08-17'],
                ['round' => 3, 'date' => '2025-08-24'],
                ['round' => 4, 'date' => '2025-08-31'],
                ['round' => 5, 'date' => '2025-09-07'],
                ['round' => 6, 'date' => '2025-09-14'],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Competition::factory()->league()->create([
            'id' => 'TEST1',
            'name' => 'Test League',
            'country' => 'XX',
            'tier' => 1,
            'season' => '2025',
            'handler_type' => 'league',
        ]);

        $this->user = User::factory()->create();

        foreach ($this->clubsPayload() as $index => $club) {
            $team = Team::factory()->create([
                'name' => $club['name'],
                'external_id' => $club['externalId'],
                'country' => 'XX',
                'stadium_seats' => 4_000 + ($index * 500),
            ]);

            CompetitionTeam::query()->create([
                'competition_id' => 'TEST1',
                'team_id' => $team->id,
                'season' => '2025',
                'entry_round' => 1,
            ]);

            if ($index === 0) {
                $this->userTeam = $team;
            }
        }
    }

    protected function tearDown(): void
    {
        @unlink($this->testDataDir . '/teams.json');
        @unlink($this->testDataDir . '/schedule.json');
        @rmdir($this->testDataDir);

        parent::tearDown();
    }

    public function test_setup_new_game_populates_user_roster_from_bom_encoded_teams_json(): void
    {
        $game = $this->makeGame(setupComplete: false);

        $this->runSetupJob($game);

        $this->assertSame(4, GamePlayer::where('game_id', $game->id)->where('team_id', $this->userTeam->id)->count());
        $this->assertSame(16, GamePlayer::where('game_id', $game->id)->count());

        $finances = GameFinances::where('game_id', $game->id)->where('season', $game->season)->first();
        $this->assertNotNull($finances);
        $this->assertGreaterThan(0, $finances->projected_wages);
        $this->assertNotNull($game->fresh()->setup_completed_at);
    }

    public function test_new_season_page_repairs_broken_completed_game_with_empty_user_squad(): void
    {
        $game = $this->makeGame(setupComplete: true);

        GameFinances::create([
            'game_id' => $game->id,
            'season' => 2025,
            'projected_total_revenue' => 500_000_000,
            'projected_wages' => 0,
            'projected_surplus' => 500_000_000,
            'projected_tv_revenue' => 200_000_000,
            'projected_matchday_revenue' => 100_000_000,
            'projected_commercial_revenue' => 100_000_000,
            'projected_operating_expenses' => 0,
            'projected_taxes' => 0,
            'projected_solidarity_funds_revenue' => 0,
            'projected_subsidy_revenue' => 0,
            'carried_debt' => 0,
            'carried_surplus' => 0,
        ]);

        $this->actingAs($this->user)
            ->get("/game/{$game->id}/new-season")
            ->assertOk();

        $game->refresh();

        $this->assertSame(4, GamePlayer::where('game_id', $game->id)->where('team_id', $this->userTeam->id)->count());
        $this->assertGreaterThan(0, $game->currentFinances->projected_wages);
    }

    public function test_setup_new_game_reassigns_duplicate_squad_numbers_within_same_team(): void
    {
        $clubs = $this->clubsPayload();
        $clubs[0]['players'][1]['number'] = '07';
        $clubs[0]['players'][2]['number'] = '07';
        $clubs[0]['players'][3]['number'] = null;

        file_put_contents($this->testDataDir . '/teams.json', $this->bomEncodedJson([
            'id' => 'TEST1',
            'code' => 'test-league-2025',
            'name' => 'Test League',
            'seasonID' => '2025',
            'clubs' => $clubs,
        ]));

        $game = $this->makeGame(setupComplete: false);

        $this->runSetupJob($game);

        $numbers = GamePlayer::where('game_id', $game->id)
            ->where('team_id', $this->userTeam->id)
            ->pluck('number')
            ->filter()
            ->values();

        $this->assertCount(4, GamePlayer::where('game_id', $game->id)->where('team_id', $this->userTeam->id)->get());
        $this->assertSame($numbers->count(), $numbers->unique()->count());
        $this->assertTrue($numbers->contains(7));
    }

    public function test_setup_new_game_skips_duplicate_player_ids_across_clubs(): void
    {
        $clubs = $this->clubsPayload();
        $duplicateId = $clubs[0]['players'][0]['id'];
        $clubs[1]['players'][0]['id'] = $duplicateId;
        $clubs[1]['players'][0]['name'] = 'Duplicated Test Player';

        file_put_contents($this->testDataDir . '/teams.json', $this->bomEncodedJson([
            'id' => 'TEST1',
            'code' => 'test-league-2025',
            'name' => 'Test League',
            'seasonID' => '2025',
            'clubs' => $clubs,
        ]));

        $game = $this->makeGame(setupComplete: false);

        $this->runSetupJob($game);

        $playerIds = GamePlayer::where('game_id', $game->id)->pluck('player_id');

        $this->assertSame($playerIds->count(), $playerIds->unique()->count());
        $this->assertSame(15, $playerIds->count());
    }

    private function makeGame(bool $setupComplete): Game
    {
        return Game::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->userTeam->id,
            'competition_id' => 'TEST1',
            'country' => 'XX',
            'season' => '2025',
            'current_date' => '2025-07-01',
            'current_matchday' => 0,
            'needs_welcome' => false,
            'needs_new_season_setup' => true,
            'game_mode' => Game::MODE_CAREER,
            'setup_completed_at' => $setupComplete ? now() : null,
        ]);
    }

    private function runSetupJob(Game $game): void
    {
        $job = new SetupNewGame(
            gameId: $game->id,
            teamId: $game->team_id,
            competitionId: $game->competition_id,
            season: $game->season,
            gameMode: $game->game_mode,
        );

        $job->handle(
            app(ContractService::class),
            app(PlayerDevelopmentService::class),
            app(SeasonSetupPipeline::class),
            app(LeagueFixtureProcessor::class),
            app(StandingsResetProcessor::class),
            app(BudgetProjectionService::class),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function clubsPayload(): array
    {
        return [
            $this->clubPayload('test1-alpha', 'Test Alpha FC'),
            $this->clubPayload('test1-beta', 'Test Beta FC'),
            $this->clubPayload('test1-gamma', 'Test Gamma FC'),
            $this->clubPayload('test1-delta', 'Test Delta FC'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clubPayload(string $externalId, string $name): array
    {
        return [
            'id' => $externalId,
            'externalId' => $externalId,
            'name' => $name,
            'image' => "https://example.test/crests/{$externalId}.png",
            'stadiumName' => "{$name} Stadium",
            'stadiumSeats' => 5000,
            'players' => [
                $this->playerPayload($externalId, '01', 'Goalkeeper', '150k'),
                $this->playerPayload($externalId, '02', 'Centre-Back', '175k'),
                $this->playerPayload($externalId, '03', 'Central Midfield', '200k'),
                $this->playerPayload($externalId, '04', 'Centre-Forward', '225k'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function playerPayload(string $clubId, string $suffix, string $position, string $marketValue): array
    {
        return [
            'id' => "{$clubId}-p{$suffix}",
            'name' => "{$clubId} Player {$suffix}",
            'dateOfBirth' => '2000-01-01',
            'nationality' => ['ESP'],
            'position' => $position,
            'marketValue' => $marketValue,
            'contract' => '2027-06-30',
            'foot' => 'right',
        ];
    }

    private function bomEncodedJson(array $payload): string
    {
        return "\xEF\xBB\xBF" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
