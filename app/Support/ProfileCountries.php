<?php

namespace App\Support;

use Locale;

/**
 * Curated list of countries for user profile selection.
 * Returns country names translated to the current app locale via PHP intl.
 *
 * @return array<string, string> ISO 3166-1 alpha-2 code (uppercase) => localized country name
 */
class ProfileCountries
{
    /** @return array<string, string> */
    public static function all(): array
    {
        $locale = app()->getLocale();
        $countries = [];
        $intlAvailable = class_exists(Locale::class) && method_exists(Locale::class, 'getDisplayRegion');

        foreach (CountryCodeMapper::getMap() as $name => $code) {
            // Skip football-specific sub-country codes (e.g. gb-eng, gb-sct)
            if (str_contains($code, '-')) {
                continue;
            }

            $upper = strtoupper($code);

            // Keep only one entry per code
            if (! isset($countries[$upper])) {
                $localized = $intlAvailable
                    ? Locale::getDisplayRegion('und_'.$upper, $locale)
                    : $upper;

                // Fall back to the curated name when intl is unavailable or cannot resolve the country.
                $countries[$upper] = ($localized !== $upper) ? $localized : $name;
            }
        }

        asort($countries);

        return $countries;
    }
}
