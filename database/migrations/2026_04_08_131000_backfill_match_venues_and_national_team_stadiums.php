<?php

use App\Models\GameMatch;
use App\Models\Team;
use App\Modules\Match\Services\MatchVenueService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $venueService = app(MatchVenueService::class);
        $seasonPaths = glob(base_path('data/2025/*/teams.json')) ?: [];

        foreach ($seasonPaths as $path) {
            $payload = json_decode((string) file_get_contents($path), true);
            $clubs = $payload['clubs'] ?? [];

            foreach ($clubs as $club) {
                $externalId = (string) ($club['externalId'] ?? $club['id'] ?? '');
                $stadiumName = trim((string) ($club['stadiumName'] ?? ''));
                $stadiumSeats = (int) preg_replace('/\D+/', '', (string) ($club['stadiumSeats'] ?? '0'));

                if ($externalId === '' || $stadiumName === '' || $stadiumSeats <= 0) {
                    continue;
                }

                DB::table('teams')
                    ->where('external_id', $externalId)
                    ->update([
                        'stadium_name' => $stadiumName,
                        'stadium_seats' => $stadiumSeats,
                    ]);
            }
        }

        foreach ((glob(base_path('data/2025/EUR/*.json')) ?: []) as $path) {
            $payload = json_decode((string) file_get_contents($path), true);
            $externalId = (string) ($payload['transfermarktId'] ?? pathinfo($path, PATHINFO_FILENAME));
            $stadiumName = trim((string) ($payload['stadiumName'] ?? ''));
            $stadiumSeats = (int) preg_replace('/\D+/', '', (string) ($payload['stadiumSeats'] ?? '0'));

            if ($externalId === '' || $stadiumName === '' || $stadiumSeats <= 0) {
                continue;
            }

            DB::table('teams')
                ->where('external_id', $externalId)
                ->update([
                    'stadium_name' => $stadiumName,
                    'stadium_seats' => $stadiumSeats,
                ]);
        }

        foreach ((array) config('stadiums.club_overrides', []) as $externalId => $override) {
            $stadiumName = trim((string) ($override['name'] ?? ''));
            $stadiumSeats = (int) ($override['capacity'] ?? 0);

            if ($stadiumName === '' || $stadiumSeats <= 0) {
                continue;
            }

            DB::table('teams')
                ->where('external_id', (string) $externalId)
                ->update([
                    'stadium_name' => $stadiumName,
                    'stadium_seats' => $stadiumSeats,
                ]);
        }

        Team::query()
            ->where('type', 'national')
            ->get()
            ->each(function (Team $team) use ($venueService) {
                $pool = $venueService->nationalTeamPool($team);
                $primaryVenue = $pool[0] ?? null;

                if (! $primaryVenue) {
                    return;
                }

                DB::table('teams')
                    ->where('id', $team->id)
                    ->update([
                        'stadium_name' => $primaryVenue['name'],
                        'stadium_seats' => $primaryVenue['capacity'],
                    ]);
            });

        GameMatch::query()
            ->where(function ($query) {
                $query->whereNull('venue_name')
                    ->orWhereNull('venue_capacity');
            })
            ->get()
            ->each(function (GameMatch $match) use ($venueService) {
                $venue = $venueService->resolve(
                    homeTeamId: $match->home_team_id,
                    competitionId: $match->competition_id,
                    roundNumber: $match->round_number,
                    matchKey: $match->id,
                );

                DB::table('game_matches')
                    ->where('id', $match->id)
                    ->update([
                        'venue_name' => $venue['venue_name'],
                        'venue_capacity' => $venue['venue_capacity'],
                    ]);
            });
    }

    public function down(): void
    {
        // Reference-data backfill only.
    }
};
