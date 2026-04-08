<?php

namespace App\Http\Actions;

use App\Models\WaitlistEntry;
use App\Services\BetaInviteService;
use Illuminate\Http\Request;

class SendWaitlistInvite
{
    public function __invoke(Request $request, WaitlistEntry $waitlistEntry, BetaInviteService $inviteService)
    {
        if (! config('beta.enabled')) {
            return back()->with('error', __('admin.waitlist_beta_disabled'));
        }

        if ($waitlistEntry->isRejected()) {
            return back()->with('error', __('admin.waitlist_cannot_invite_rejected'));
        }

        if ($inviteService->hasAlreadyBeenInvited($waitlistEntry->email)) {
            return back()->with('error', __('admin.waitlist_already_invited'));
        }

        $inviteService->invite($waitlistEntry);

        return back()->with('success', __('admin.waitlist_invite_sent'));
    }
}
