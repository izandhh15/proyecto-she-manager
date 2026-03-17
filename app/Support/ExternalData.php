<?php

namespace App\Support;

final class ExternalData
{
    public const SOURCE_SOCCERDONNA = 'soccerdonna';
    public const SOURCE_TRANSFERMARKT = 'transfermarkt';

    public static function defaultSource(): string
    {
        return self::SOURCE_SOCCERDONNA;
    }

    public static function clubExternalId(array $club): ?string
    {
        $id = $club['externalId']
            ?? $club['transfermarktId']
            ?? $club['id']
            ?? self::extractIdFromImage($club['image'] ?? '');

        return $id !== null ? (string) $id : null;
    }

    public static function playerExternalId(array $player): ?string
    {
        $id = $player['externalId'] ?? $player['id'] ?? null;

        return $id !== null ? (string) $id : null;
    }

    public static function mappingExternalId(array $teamData): ?string
    {
        $id = $teamData['external_id'] ?? $teamData['transfermarkt_id'] ?? null;

        return $id !== null ? (string) $id : null;
    }

    public static function extractIdFromImage(string $imageUrl): ?string
    {
        if (preg_match('/\/(\d+)\.png$/', $imageUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
