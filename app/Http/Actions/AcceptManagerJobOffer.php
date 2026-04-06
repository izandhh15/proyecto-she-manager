<?php

namespace App\Http\Actions;

use App\Models\Game;
use App\Models\ManagerJobOffer;

class AcceptManagerJobOffer
{
    public function __invoke(string $gameId, int $offerId)
    {
        $game = Game::findOrFail($gameId);

        $offer = ManagerJobOffer::query()
            ->where('id', $offerId)
            ->where('game_id', $gameId)
            ->where('status', ManagerJobOffer::STATUS_PENDING)
            ->firstOrFail();

        if ($offer->offer_type === ManagerJobOffer::TYPE_NATIONAL) {
            $game->update([
                'national_team_id' => $offer->team_id,
            ]);
        } else {
            $game->update([
                'team_id' => $offer->team_id,
                'competition_id' => $offer->competition_id,
                'is_sacked' => false,
            ]);
        }

        $offer->update(['status' => ManagerJobOffer::STATUS_ACCEPTED]);

        ManagerJobOffer::query()
            ->where('game_id', $gameId)
            ->where('id', '!=', $offer->id)
            ->where('status', ManagerJobOffer::STATUS_PENDING)
            ->update(['status' => ManagerJobOffer::STATUS_DECLINED]);

        return redirect()->route('game.season-end', $gameId)
            ->with('success', __('messages.offer_accepted'));
    }
}
