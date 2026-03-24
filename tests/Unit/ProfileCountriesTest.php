<?php

namespace Tests\Unit;

use App\Support\ProfileCountries;
use Tests\TestCase;

class ProfileCountriesTest extends TestCase
{
    public function test_it_returns_a_country_list_even_without_intl_locale_support(): void
    {
        $countries = ProfileCountries::all();

        $this->assertIsArray($countries);
        $this->assertNotEmpty($countries);
        $this->assertSame('Spain', $countries['ES'] ?? null);
        $this->assertSame('United States', $countries['US'] ?? null);
    }
}
