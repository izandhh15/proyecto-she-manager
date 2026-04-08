<?php

namespace Tests\Feature;

use App\Mail\BetaInvite;
use App\Models\InviteCode;
use App\Models\User;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_waitlist_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $entry = WaitlistEntry::create([
            'name' => 'Izan Delgado',
            'email' => 'izan@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.waitlist'))
            ->assertOk()
            ->assertSee($entry->email);
    }

    public function test_admin_can_send_waitlist_invite(): void
    {
        Mail::fake();
        config(['beta.enabled' => true]);

        $admin = User::factory()->create(['is_admin' => true]);
        $entry = WaitlistEntry::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.send-waitlist-invite', $entry))
            ->assertRedirect();

        $this->assertDatabaseHas('invite_codes', [
            'email' => 'pending@example.com',
            'max_uses' => 1,
        ]);

        Mail::assertSent(BetaInvite::class);
    }

    public function test_admin_can_reject_pending_waitlist_entry(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $entry = WaitlistEntry::create([
            'name' => 'Rejected User',
            'email' => 'rejected@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reject-waitlist-entry', $entry))
            ->assertRedirect();

        $this->assertNotNull($entry->fresh()->rejected_at);
    }

    public function test_admin_cannot_reject_entry_that_has_already_been_invited(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $entry = WaitlistEntry::create([
            'name' => 'Invited User',
            'email' => 'invited@example.com',
        ]);

        InviteCode::create([
            'code' => 'INVITED1',
            'email' => $entry->email,
            'max_uses' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reject-waitlist-entry', $entry))
            ->assertRedirect();

        $this->assertNull($entry->fresh()->rejected_at);
    }
}
