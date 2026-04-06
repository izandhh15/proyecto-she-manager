<?php

namespace App\Http\Actions;

use App\Models\Game;
use App\Models\GameMatch;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestPreSeasonFriendly
{
    public function __invoke(Request $request, string $gameId)
    {
        $game = Game::findOrFail($gameId);

        if (! $game->isInPreSeason()) {
            return redirect()->route('show-game', $gameId);
        }

        $validated = $request->validate([
            'opponent_team_id' => ['required', 'string', 'exists:teams,id'],
            'round_number' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $competitionId = config('preseason.competition_id', 'PRESEASON');
        $schedule = collect(config('preseason.schedule', []))->values();
        $seasonYear = (int) $game->season;

        $opponent = Team::findOrFail($validated['opponent_team_id']);
        if ($opponent->id === $game->team_id) {
            return redirect()->route('show-game', $gameId)->with('error', __('messages.pre_season_invalid_opponent'));
        }

        if (($opponent->country ?? 'XX') === ($game->country ?? 'ES')) {
            return redirect()->route('show-game', $gameId)->with('error', __('messages.pre_season_foreign_only'));
        }

        $requestedRound = isset($validated['round_number']) ? (int) $validated['round_number'] : null;
        $availableSlots = $schedule->filter(function (array $slot, int $index) use ($game, $competitionId, $requestedRound) {
            $round = $index + 1;
            if ($requestedRound && $round !== $requestedRound) {
                return false;
            }

            return ! GameMatch::where('game_id', $game->id)
                ->where('competition_id', $competitionId)
                ->where('round_number', $round)
                ->exists();
        });

        if ($availableSlots->isEmpty()) {
            return redirect()->route('show-game', $gameId)->with('error', __('messages.pre_season_no_slots'));
        }

        $slotIndex = $availableSlots->keys()->first();
        $slot = $schedule[$slotIndex];
        $round = $slotIndex + 1;
        $date = Carbon::createFromDate($seasonYear, $slot['month'], $slot['day'])->toDateString();

        $opponentBusy = GameMatch::where('game_id', $game->id)
            ->whereDate('scheduled_date', $date)
            ->where(function ($q) use ($opponent) {
                $q->where('home_team_id', $opponent->id)
                    ->orWhere('away_team_id', $opponent->id);
            })
            ->exists();

        if ($opponentBusy) {
            return redirect()->route('show-game', $gameId)->with('error', __('messages.pre_season_opponent_busy'));
        }

        GameMatch::create([
            'id' => Str::uuid()->toString(),
            'game_id' => $game->id,
            'competition_id' => $competitionId,
            'home_team_id' => ! empty($slot['home']) ? $game->team_id : $opponent->id,
            'away_team_id' => ! empty($slot['home']) ? $opponent->id : $game->team_id,
            'scheduled_date' => $date,
            'round_number' => $round,
            'played' => false,
        ]);

        return redirect()->route('show-game', $gameId)->with('success', __('messages.pre_season_friendly_requested', [
            'team' => $opponent->name,
        ]));
    }
}

