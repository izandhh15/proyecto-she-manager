<?php

namespace Tests\Unit;

use App\Modules\Season\Jobs\SetupTournamentGame;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SetupTournamentGameTest extends TestCase
{
    public function test_non_qualified_selected_team_replaces_placeholder_slot(): void
    {
        $job = new SetupTournamentGame('game-1', 'team-andorra');

        $groupsData = [
            'A' => ['teams' => ['ESP', 'UEPD'], 'matches' => []],
        ];
        $mapping = [
            'ESP' => ['uuid' => 'team-spain', 'is_placeholder' => false],
            'UEPD' => ['uuid' => 'placeholder-a', 'is_placeholder' => true],
        ];

        $method = new ReflectionMethod($job, 'resolveTournamentTeamKeyMap');
        $method->setAccessible(true);
        $resolved = $method->invoke($job, $groupsData, $mapping);

        $this->assertSame('team-spain', $resolved['ESP']);
        $this->assertSame('team-andorra', $resolved['UEPD']);
    }
}
