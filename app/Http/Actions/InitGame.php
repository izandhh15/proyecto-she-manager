<?php

namespace App\Http\Actions;

use App\Models\ActivationEvent;
use App\Models\Competition;
use App\Modules\Season\Services\ActivationTracker;
use App\Modules\Season\Services\GameCreationService;
use App\Modules\Season\Services\TournamentCreationService;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InitGame
{
    public function __construct(
        private readonly GameCreationService $gameCreationService,
        private readonly TournamentCreationService $tournamentCreationService,
        private readonly ActivationTracker $activationTracker,
    ) {}

    public function __invoke(Request $request)
    {
        $gameCount = Game::where('user_id', $request->user()->id)->count();
        if ($gameCount >= 3) {
            return back()->withErrors(['limit' => __('messages.game_limit_reached')]);
        }

        $request->validate([
            'management_mode' => ['required', Rule::in(['club_only', 'national_only', 'club_national'])],
            'club_team_id' => ['nullable', 'uuid'],
            'national_team_id' => ['nullable', 'uuid'],
        ]);

        $managementMode = $request->string('management_mode')->toString();
        $clubTeamId = $request->input('club_team_id');
        $nationalTeamId = $request->input('national_team_id');

        if ($managementMode === 'national_only') {
            if (! $nationalTeamId) {
                return back()->withErrors(['national_team_id' => __('game.national_team_required')])->withInput();
            }

            if (! Competition::where('id', 'WC2026')->exists()) {
                return back()->withErrors(['national_team_id' => __('game.national_mode_unavailable')])->withInput();
            }

            $game = $this->tournamentCreationService->create(
                userId: (string) $request->user()->id,
                teamId: $nationalTeamId,
            );

            $this->activationTracker->record($request->user()->id, ActivationEvent::EVENT_GAME_CREATED, $game->id, Game::MODE_TOURNAMENT);

            return redirect()->route('show-game', $game->id);
        }

        if (! $clubTeamId) {
            return back()->withErrors(['club_team_id' => __('game.club_team_required')])->withInput();
        }

        if ($managementMode === 'club_national' && ! $nationalTeamId) {
            return back()->withErrors(['national_team_id' => __('game.national_team_required')])->withInput();
        }

        if ($managementMode === 'club_national' && $clubTeamId === $nationalTeamId) {
            return back()->withErrors(['national_team_id' => __('game.national_team_must_differ')])->withInput();
        }

        $game = $this->gameCreationService->create(
            userId: (string) $request->user()->id,
            teamId: $clubTeamId,
            gameMode: Game::MODE_CAREER,
            nationalTeamId: $managementMode === 'club_national' ? $nationalTeamId : null,
        );

        $this->activationTracker->record($request->user()->id, ActivationEvent::EVENT_GAME_CREATED, $game->id, Game::MODE_CAREER);

        return redirect()->route('game.welcome', $game->id);
    }
}
