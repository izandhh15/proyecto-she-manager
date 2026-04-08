<?php

namespace App\Http\Actions;

use App\Modules\Academy\Services\YouthAcademyService;
use App\Models\AcademyPlayer;
use App\Models\Game;

class LoanAcademyPlayer
{
    public function __construct(
        private readonly YouthAcademyService $youthAcademyService,
    ) {}

    public function __invoke(string $gameId, string $playerId)
    {
        $game = Game::findOrFail($gameId);

        $academy = AcademyPlayer::where('id', $playerId)
            ->where('game_id', $gameId)
            ->where('team_id', $game->team_id)
            ->where('is_on_loan', false)
            ->firstOrFail();

        if ($academy->is_reserve_linked) {
            return redirect()->route('game.squad.academy', $gameId)
                ->with('error', __('messages.academy_reserve_action_blocked'));
        }

        $playerName = $academy->name;

        $this->youthAcademyService->loanPlayer($academy);

        return redirect()->route('game.squad.academy', $gameId)
            ->with('success', __('messages.academy_player_loaned', ['player' => $playerName]));
    }
}
