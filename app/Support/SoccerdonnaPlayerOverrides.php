<?php

namespace App\Support;

use Carbon\CarbonImmutable;

final class SoccerdonnaPlayerOverrides
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private static array $cache = [];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function load(string $season): array
    {
        if (isset(self::$cache[$season])) {
            return self::$cache[$season];
        }

        $path = base_path("data/{$season}/soccerdonna_player_overrides.json");
        if (! file_exists($path)) {
            return self::$cache[$season] = [];
        }

        $data = ExternalData::decodeJsonFile($path);
        $players = $data['players'] ?? $data;

        return self::$cache[$season] = is_array($players) ? $players : [];
    }

    /**
     * @param  array<string, mixed>  $player
     * @return array<string, mixed>
     */
    public static function apply(string $season, array $player): array
    {
        $externalId = ExternalData::playerExternalId($player);
        if (! $externalId) {
            return $player;
        }

        $overrides = self::load($season)[$externalId] ?? null;
        if (! is_array($overrides)) {
            return $player;
        }

        foreach (['name', 'position', 'number', 'dateOfBirth', 'foot', 'height', 'marketValue', 'contract'] as $field) {
            if (array_key_exists($field, $overrides) && $overrides[$field] !== null && $overrides[$field] !== '') {
                $player[$field] = $overrides[$field];
            }
        }

        if (! empty($overrides['nationality']) && is_array($overrides['nationality'])) {
            $player['nationality'] = array_values(array_unique(array_filter(
                array_map('strval', $overrides['nationality']),
                fn (string $value) => $value !== ''
            )));
        }

        if (isset($overrides['age']) && is_numeric($overrides['age'])) {
            $player['age'] = (int) $overrides['age'];
        }

        if (empty($player['age']) && ! empty($player['dateOfBirth'])) {
            try {
                $player['age'] = CarbonImmutable::parse((string) $player['dateOfBirth'])->age;
            } catch (\Throwable) {
                // Keep the imported value when the override date is not parseable.
            }
        }

        return $player;
    }
}
