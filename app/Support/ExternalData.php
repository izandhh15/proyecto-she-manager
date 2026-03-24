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

    /**
     * Decode a JSON file defensively, tolerating BOM-prefixed UTF encodings.
     *
     * @return array<string, mixed>
     */
    public static function decodeJsonFile(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException("Unable to read JSON file: {$path}");
        }

        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        } elseif (str_starts_with($contents, "\xFF\xFE")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($contents, "\xFE\xFF")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        }

        $data = json_decode($contents, true);

        if (! is_array($data)) {
            throw new \RuntimeException("Invalid JSON in {$path}: " . json_last_error_msg());
        }

        return $data;
    }
}
