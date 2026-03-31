<?php

namespace Tests\Unit;

use App\Modules\Player\Services\InjuryService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class InjuryServiceTest extends TestCase
{
    public function test_acl_tear_is_forced_to_season_end_when_base_duration_is_shorter(): void
    {
        $matchDate = Carbon::parse('2025-09-14');

        $injuryUntil = InjuryService::resolveInjuryUntil('ACL tear', 34, $matchDate);

        $this->assertTrue(InjuryService::isSeasonEndingInjury('ACL tear'));
        $this->assertSame('2026-06-30', $injuryUntil->toDateString());
    }

    public function test_non_season_ending_injury_keeps_regular_duration(): void
    {
        $matchDate = Carbon::parse('2025-09-14');

        $injuryUntil = InjuryService::resolveInjuryUntil('Hamstring tear', 4, $matchDate);

        $this->assertFalse(InjuryService::isSeasonEndingInjury('Hamstring tear'));
        $this->assertSame('2025-10-12', $injuryUntil->toDateString());
    }
}
