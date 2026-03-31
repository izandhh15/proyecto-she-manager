<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NationalTeamSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_game_lists_non_qualified_national_teams_when_seeded(): void
    {
        Competition::factory()->league()->create([
            'id' => 'WC2026',
            'role' => 'league',
            'tier' => 0,
        ]);

        Team::factory()->create([
            'type' => 'national',
            'name' => 'Andorra',
            'country' => 'ad',
            'external_id' => 'andorra-nt',
            'external_source' => 'soccerdonna',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('select-team'));

        $response->assertOk();
        $response->assertSee('Andorra');
    }
}
