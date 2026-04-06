<?php

namespace App\Http\Actions;

use App\Models\Game;
use App\Models\GameMatch;

class CancelPreSeasonFriendly
{
    public function __invoke(string $gameId, string $matchId)
    {
        $game = Game::findOrFail($gameId);

        if (! $game->isInPreSeason()) {
            return redirect()->route('show-game', $gameId);
        }

        $competitionId = config('preseason.competition_id', 'PRESEASON');

        $match = GameMatch::where('id', $matchId)
            ->where('game_id', $game->id)
            ->where('competition_id', $competitionId)
            ->where('played', false)
            ->where(function ($q) use ($game) {
                $q->where('home_team_id', $game->team_id)
                    ->orWhere('away_team_id', $game->team_id);
            })
            ->first();

        if (! $match) {
            return redirect()->route('show-game', $gameId)->with('error', __('messages.pre_season_friendly_not_found'));
        }

        $match->delete();

        return redirect()->route('show-game', $gameId)->with('success', __('messages.pre_season_friendly_cancelled'));
    }
}

