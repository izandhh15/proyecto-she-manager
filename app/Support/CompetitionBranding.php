<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class CompetitionBranding
{
    private const LOGOS = [
        'ESP1' => 'competition-logos/esp1-ligaf.svg',
        'ESP2' => 'competition-logos/esp2-primera-federacion-futfem.svg',
        'ESPCUP' => 'competition-logos/espcup-copa-reina.svg',
        'ESPSUP' => 'competition-logos/espsup-supercopa-femenina.svg',
        'UCL' => 'competition-logos/ucl-women.svg',
        'UEL' => 'competition-logos/uel-women.svg',
    ];

    public static function hasLogo(mixed $competition): bool
    {
        return self::logoPath($competition) !== null;
    }

    public static function logoPath(mixed $competition): ?string
    {
        $competitionId = data_get($competition, 'id');
        $path = $competitionId ? (self::LOGOS[$competitionId] ?? null) : null;

        if (! $path || ! is_file(public_path($path))) {
            return null;
        }

        return $path;
    }

    public static function logoUrl(mixed $competition): ?string
    {
        $path = self::logoPath($competition);

        return $path ? Storage::disk('assets')->url($path) : null;
    }

    public static function flagUrl(mixed $competition): ?string
    {
        $flag = data_get($competition, 'flag');

        if (! $flag) {
            return null;
        }

        $path = 'flags/' . strtolower($flag) . '.svg';

        if (! is_file(public_path($path))) {
            return null;
        }

        return Storage::disk('assets')->url($path);
    }

    public static function iconUrl(mixed $competition): ?string
    {
        return self::logoUrl($competition) ?? self::flagUrl($competition);
    }
}
