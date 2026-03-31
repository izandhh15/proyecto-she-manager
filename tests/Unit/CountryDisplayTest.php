<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Support\CountryCodeMapper;
use Tests\TestCase;

class CountryDisplayTest extends TestCase
{
    public function test_national_team_name_falls_back_to_raw_country_name_when_translation_is_missing(): void
    {
        $team = new Team([
            'type' => 'national',
            'name' => 'Bermuda',
            'country' => 'bm',
        ]);

        $this->assertSame('Bermuda', $team->name);
    }

    public function test_national_team_flag_falls_back_to_name_mapping_when_country_code_is_tbd(): void
    {
        $team = new Team([
            'type' => 'national',
            'name' => 'Scottland',
            'country' => 'TBD',
        ]);

        $this->assertStringEndsWith('/flags/gb-sct.svg', $team->image);
    }

    public function test_soccerdonna_aliases_resolve_to_flag_codes(): void
    {
        $this->assertSame('tw', CountryCodeMapper::toCode('Chinese Taipei (Taiwan)'));
        $this->assertSame('ss', CountryCodeMapper::toCode('Southern Sudan'));
        $this->assertSame('vc', CountryCodeMapper::toCode('St. Vincent & Grenadinen'));
    }
}
