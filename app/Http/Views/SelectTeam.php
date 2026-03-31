<?php

namespace App\Http\Views;

use App\Modules\Competition\Services\CountryConfig;
use App\Models\Competition;
use App\Models\Game;
use App\Models\Team;
use Illuminate\Http\Request;

final class SelectTeam
{
    public function __invoke(Request $request, CountryConfig $countryConfig)
    {
        if (Game::where('user_id', $request->user()->id)->count() >= 3) {
            return redirect()->route('dashboard')->withErrors(['limit' => __('messages.game_limit_reached')]);
        }

        // Build country â†’ tier â†’ competition structure for career mode
        $countries = [];

        foreach ($countryConfig->playableCountryCodes() as $code) {
            $config = $countryConfig->get($code);
            $tiers = [];

            foreach ($config['tiers'] as $tier => $tierConfig) {
                $competition = Competition::with('teams')
                    ->find($tierConfig['competition']);

                if ($competition) {
                    $tiers[$tier] = $competition;
                }
            }

            if (!empty($tiers)) {
                $countries[$code] = [
                    'name' => $config['name'],
                    'tiers' => $tiers,
                ];
            }
        }

        // Load World Cup teams for tournament mode
        $wcTeams = collect();
        $wcFeaturedTeams = collect();
        $hasTournamentMode = Competition::where('id', 'WC2026')->exists();

        if ($hasTournamentMode) {
            $allWcTeams = Team::query()
                ->where('type', 'national')
                ->whereNotNull('external_id')
                ->orderBy('name')
                ->get()
                ->values();

            $featuredCountries = ['es', 'ar', 'br', 'gb-eng', 'fr', 'de', 'pt', 'nl', 'it', 'us'];
            $wcFeaturedTeams = $allWcTeams
                ->filter(fn (Team $team) => in_array(strtolower((string) $team->country), $featuredCountries, true))
                ->values();

            $wcTeams = $allWcTeams
                ->reject(fn (Team $team) => in_array($team->id, $wcFeaturedTeams->pluck('id')->all(), true))
                ->values();
        }

        return view('select-team', [
            'countries' => $countries,
            'wcTeams' => $wcTeams,
            'wcFeaturedTeams' => $wcFeaturedTeams,
            'hasTournamentMode' => $hasTournamentMode,
        ]);
    }
}
