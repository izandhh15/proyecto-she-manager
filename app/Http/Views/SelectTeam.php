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

            if (! empty($tiers)) {
                $countries[$code] = [
                    'name' => $config['name'],
                    'tiers' => $tiers,
                ];
            }
        }

        $allNationalTeams = Team::query()
            ->where(function ($query) {
                $query->where('type', 'national')
                    ->orWhereIn('id', function ($subQuery) {
                        $subQuery->select('team_id')
                            ->from('competition_teams')
                            ->where('competition_id', 'WC2026');
                    });
            })
            ->orderBy('name')
            ->get()
            ->reject(function (Team $team) {
                $name = mb_strtolower($team->name);

                // Exclude youth national teams such as U17/U-19/Sub 20
                return preg_match('/\b(?:sub|u)\s*-?\d{2}\b/u', $name) === 1;
            })
            ->unique('id')
            ->values();

        return view('select-team', [
            'countries' => $countries,
            'nationalTeams' => $allNationalTeams,
            'hasTournamentMode' => Competition::where('id', 'WC2026')->exists(),
        ]);
    }
}

