<?php

namespace App\Modules\Match\Services;

use App\Models\Team;
use Illuminate\Support\Str;

class MatchVenueService
{
    /** @var array<string, array<int, array{name: string, capacity: int}>> */
    private array $derivedPoolCache = [];

    /**
     * Resolve the venue stored on a match row.
     *
     * @return array{venue_name: ?string, venue_capacity: ?int}
     */
    public function resolve(
        string $homeTeamId,
        ?string $competitionId = null,
        ?int $roundNumber = null,
        ?string $matchKey = null,
    ): array {
        $homeTeam = Team::find($homeTeamId);

        if (! $homeTeam) {
            return [
                'venue_name' => null,
                'venue_capacity' => null,
            ];
        }

        if (($homeTeam->getAttribute('type') ?? 'club') !== 'national') {
            $clubOverride = $this->clubOverride($homeTeam);

            return [
                'venue_name' => $clubOverride['name'] ?? $homeTeam->stadium_name,
                'venue_capacity' => $clubOverride['capacity']
                    ?? ($homeTeam->stadium_seats > 0 ? $homeTeam->stadium_seats : null),
            ];
        }

        $pool = $this->nationalTeamPool($homeTeam);

        if ($pool === []) {
            return [
                'venue_name' => $homeTeam->stadium_name,
                'venue_capacity' => $homeTeam->stadium_seats > 0 ? $homeTeam->stadium_seats : null,
            ];
        }

        $seed = implode('|', [
            $homeTeamId,
            $competitionId ?? '',
            (string) ($roundNumber ?? 0),
            $matchKey ?? '',
        ]);

        $index = abs(crc32($seed)) % count($pool);
        $venue = $pool[$index];

        return [
            'venue_name' => $venue['name'],
            'venue_capacity' => $venue['capacity'],
        ];
    }

    /**
     * @return array<int, array{name: string, capacity: int}>
     */
    public function nationalTeamPool(Team $nationalTeam): array
    {
        $countryCode = strtolower((string) $nationalTeam->country);

        $configured = $this->normalizePool(config("stadiums.national_team_pools.{$countryCode}", []));
        $derived = $this->derivedCountryPool($countryCode);

        return $this->uniqueVenues(array_merge($configured, $derived));
    }

    /**
     * @return array<int, array{name: string, capacity: int}>
     */
    private function derivedCountryPool(string $countryCode): array
    {
        if (isset($this->derivedPoolCache[$countryCode])) {
            return $this->derivedPoolCache[$countryCode];
        }

        $teams = Team::query()
            ->where('country', $countryCode)
            ->where(function ($query) {
                $query->whereNull('type')
                    ->orWhere('type', 'club');
            })
            ->whereNotNull('stadium_name')
            ->where('stadium_name', '!=', '')
            ->where('stadium_seats', '>', 0)
            ->orderByDesc('stadium_seats')
            ->get(['stadium_name', 'stadium_seats']);

        $venues = [];

        foreach ($teams as $team) {
            $venues[] = [
                'name' => (string) $team->stadium_name,
                'capacity' => (int) $team->stadium_seats,
            ];
        }

        $venues = $this->uniqueVenues($venues);

        if ($countryCode !== 'es') {
            $venues = array_slice($venues, 0, 5);
        }

        return $this->derivedPoolCache[$countryCode] = $venues;
    }

    /**
     * @param  array<int, array{name?: mixed, capacity?: mixed}>  $pool
     * @return array<int, array{name: string, capacity: int}>
     */
    private function normalizePool(array $pool): array
    {
        $normalized = [];

        foreach ($pool as $venue) {
            $name = trim((string) ($venue['name'] ?? ''));
            $capacity = (int) ($venue['capacity'] ?? 0);

            if ($name === '' || $capacity <= 0) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'capacity' => $capacity,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array{name: string, capacity: int}>  $venues
     * @return array<int, array{name: string, capacity: int}>
     */
    private function uniqueVenues(array $venues): array
    {
        $unique = [];

        foreach ($venues as $venue) {
            $key = mb_strtolower(trim($venue['name']));
            if ($key === '' || isset($unique[$key])) {
                continue;
            }

            $unique[$key] = $venue;
        }

        return array_values($unique);
    }

    /**
     * @return array{name: string, capacity: int}|null
     */
    private function clubOverride(Team $team): ?array
    {
        $overrides = config('stadiums.club_overrides', []);
        $candidates = array_filter([
            (string) $team->external_id,
            $team->name ? Str::slug($team->name) : null,
        ]);

        foreach ($candidates as $key) {
            $override = $overrides[$key] ?? null;
            if (! is_array($override)) {
                continue;
            }

            $name = trim((string) ($override['name'] ?? ''));
            $capacity = (int) ($override['capacity'] ?? 0);

            if ($name !== '' && $capacity > 0) {
                return [
                    'name' => $name,
                    'capacity' => $capacity,
                ];
            }
        }

        return null;
    }
}
