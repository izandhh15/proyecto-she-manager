<?php

namespace App\Http\Views;

use App\Models\ActivationEvent;
use App\Models\Game;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminActivation
{
    public function __invoke(Request $request)
    {
        $period = $request->get('period', '30');
        $mode = $request->get('mode', 'all');
        $since = $period === 'all' ? null : now()->subDays((int) $period);

        // Validate mode
        if (! in_array($mode, ['all', Game::MODE_CAREER, Game::MODE_TOURNAMENT])) {
            $mode = 'all';
        }

        // Step 0: Invite sent (from invite_codes table directly — not mode-specific)
        $inviteQuery = InviteCode::where('invite_sent', true);
        if ($since) {
            $inviteQuery->where('invite_sent_at', '>=', $since);
        }
        $inviteSentCount = $inviteQuery->count();

        // Cohort approach: scope all funnel steps to users who registered in this period.
        // This prevents later steps (e.g. game_created) from exceeding earlier steps
        // when a user registered outside the window but created a game inside it.
        $cohortSubquery = ActivationEvent::query()
            ->select('user_id')
            ->where('event', ActivationEvent::EVENT_REGISTERED)
            ->when($since, fn ($q) => $q->where('occurred_at', '>=', $since));

        $query = ActivationEvent::query()
            ->select('event', DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->whereIn('user_id', $cohortSubquery)
            ->when($mode !== 'all', fn ($q) => $q->where(function ($q) use ($mode) {
                // User-level events (no game_mode) are included in all tabs
                $q->where('game_mode', $mode)->orWhereNull('game_mode');
            }))
            ->groupBy('event');

        $eventCounts = $query->pluck('user_count', 'event');

        // Build funnel steps based on mode
        $funnelEvents = ActivationEvent::funnelForMode($mode === 'all' ? null : $mode);

        $steps = [
            [
                'key' => 'invite_sent',
                'label' => __('admin.funnel_invite_sent'),
                'count' => $inviteSentCount,
            ],
        ];

        foreach ($funnelEvents as $event) {
            $steps[] = [
                'key' => $event,
                'label' => __("admin.funnel_{$event}"),
                'count' => $eventCounts[$event] ?? 0,
            ];
        }

        // Calculate drop-off percentages
        $maxCount = max(1, $steps[0]['count']);
        foreach ($steps as $i => &$step) {
            $step['percentage'] = $maxCount > 0 ? round(($step['count'] / $maxCount) * 100, 1) : 0;
            $step['drop_off'] = $i > 0 && $steps[$i - 1]['count'] > 0
                ? round((1 - $step['count'] / $steps[$i - 1]['count']) * 100, 1)
                : 0;
        }
        unset($step);

        // Summary stats
        $registeredCount = $eventCounts[ActivationEvent::EVENT_REGISTERED] ?? 0;
        $firstMatchCount = $eventCounts[ActivationEvent::EVENT_FIRST_MATCH_PLAYED] ?? 0;
        $overallConversion = $registeredCount > 0
            ? round(($firstMatchCount / $registeredCount) * 100, 1)
            : 0;

        return view('admin.activation', [
            'steps' => $steps,
            'period' => $period,
            'mode' => $mode,
            'overallConversion' => $overallConversion,
            'totalInvites' => $inviteSentCount,
            'totalRegistered' => $registeredCount,
        ]);
    }
}
