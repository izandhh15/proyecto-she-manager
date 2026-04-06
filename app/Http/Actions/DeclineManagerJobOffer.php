<?php

namespace App\Http\Actions;

use App\Models\Game;
use App\Models\ManagerJobOffer;

class DeclineManagerJobOffer
{
    public function __invoke(string $gameId, int $offerId)
    {
        Game::findOrFail($gameId);

        $offer = ManagerJobOffer::query()
            ->where('id', $offerId)
            ->where('game_id', $gameId)
            ->where('status', ManagerJobOffer::STATUS_PENDING)
            ->firstOrFail();

        $offer->update(['status' => ManagerJobOffer::STATUS_DECLINED]);

        return redirect()->route('game.season-end', $gameId)
            ->with('success', __('messages.offer_declined'));
    }
}
