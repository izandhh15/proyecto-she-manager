<?php

namespace App\Support;

class Money
{
    private const EURO_SYMBOL = '€';

    /**
     * Format an amount in cents to a display string.
     *
     * @param int $cents Amount in cents
     * @return string e.g., "€ 2,5M", "€ 450K", "€ 200"
     */
    public static function format(int $cents): string
    {
        $isNegative = $cents < 0;
        $euros = abs($cents) / 100;
        $prefix = $isNegative ? '-' : '';

        if ($euros >= 1_000_000) {
            $formatted = self::formatCompactNumber(round($euros / 1_000_000, 1));

            return "{$prefix}" . self::EURO_SYMBOL . " {$formatted}M";
        }

        if ($euros >= 1_000) {
            $formatted = round($euros / 1_000);

            return "{$prefix}" . self::EURO_SYMBOL . " {$formatted}K";
        }

        return "{$prefix}" . self::EURO_SYMBOL . ' ' . number_format($euros, 0, ',', '.');
    }

    /**
     * Format with explicit sign prefix for positive values.
     *
     * @param int $cents Amount in cents
     * @return string e.g., "+€ 2,5M", "-€ 450K"
     */
    public static function formatSigned(int $cents): string
    {
        if ($cents >= 0) {
            return '+' . self::format($cents);
        }

        return self::format($cents);
    }

    /**
     * Parse a market value string like "€10M" or "500k" into cents.
     *
     * @param string|null $value e.g., "€10M", "€500K", "250000"
     * @return int Amount in cents
     */
    public static function parseMarketValue(?string $value): int
    {
        if (! $value) {
            return 0;
        }

        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[€$£\s]/u', '', $value);

        if (preg_match('/^([\d.]+)(m|k)?$/i', $value, $matches)) {
            $number = (float) $matches[1];
            $multiplier = strtolower($matches[2] ?? '');

            $amount = match ($multiplier) {
                'm' => $number * 1_000_000,
                'k' => $number * 1_000,
                default => $number,
            };

            return (int) ($amount * 100);
        }

        return 0;
    }

    private static function formatCompactNumber(float $number): string
    {
        if ($number == (int) $number) {
            return (string) (int) $number;
        }

        return str_replace('.', ',', number_format($number, 1, '.', ''));
    }
}
