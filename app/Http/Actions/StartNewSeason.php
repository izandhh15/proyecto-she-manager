<?php

namespace App\Http\Actions;

use App\Modules\Season\Jobs\ProcessSeasonTransition;
use App\Models\Game;
use App\Models\ManagerJobOffer;

class StartNewSeason
{
    public function __invoke(string $gameId)
    {
        $game = Game::findOrFail($gameId);

        // Verify season is complete
        $unplayedMatches = $game->matches()->where('played', false)->count();
        if ($unplayedMatches > 0) {
            return redirect()->route('show-game', $gameId)
                ->with('error', __('messages.season_not_complete'));
        }

        if ($game->is_sacked) {
            $hasPendingClubOffers = ManagerJobOffer::query()
                ->where('game_id', $game->id)
                ->where('offer_type', ManagerJobOffer::TYPE_CLUB)
                ->where('status', ManagerJobOffer::STATUS_PENDING)
                ->exists();

            if ($hasPendingClubOffers) {
                return redirect()->route('game.season-end', $gameId)
                    ->with('error', __('messages.must_accept_club_offer'));
            }
        }

        // Atomic check-and-set: only one request can win the race
        $updated = Game::where('id', $gameId)
            ->whereNull('season_transitioning_at')
            ->update(['season_transitioning_at' => now()]);

        if (! $updated) {
            return redirect()->route('show-game', $gameId);
        }

        ProcessSeasonTransition::dispatch($gameId);

        return redirect()->route('show-game', $gameId);
    }
}
