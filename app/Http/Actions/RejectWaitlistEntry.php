<?php

namespace App\Http\Actions;

use App\Models\WaitlistEntry;
use Illuminate\Http\Request;

class RejectWaitlistEntry
{
    public function __invoke(Request $request, WaitlistEntry $waitlistEntry)
    {
        if ($waitlistEntry->inviteCode) {
            return back()->with('error', __('admin.waitlist_cannot_reject_invited'));
        }

        if ($waitlistEntry->isRejected()) {
            return back()->with('error', __('admin.waitlist_already_rejected'));
        }

        $waitlistEntry->update([
            'rejected_at' => now(),
        ]);

        return back()->with('success', __('admin.waitlist_rejected'));
    }
}
