<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Waiter;
use App\Notifications\EventAssignedNotification;
use App\Notifications\EventDeclinedNotification;
use App\Notifications\WaiterConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchedulingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name' => 'Test Admin',
            'role' => 'admin',
        ]);
    }

    private function waiter(string $name = 'Test Waiter'): Waiter
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => 'waiter',
        ]);

        return Waiter::create([
            'user_id' => $user->id,
            'phone' => '09123456789',
            'status' => 'active',
        ]);
    }

    private function event(User $admin, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'event_name' => 'Test Birthday Event',
            'venue_address' => 'San Miguel, Nabua, Camarines Sur',
            'event_date' => now()->addDays(14),
            'setup_time' => now()->addDays(14)->setTime(11, 0),
            'guest_count' => 50,
            'status' => 'scheduled',
            'notes' => 'Test event',
            'created_by' => $admin->id,
        ], $overrides));
    }

    /** TC-SCH-A01: Admin Creates a New Event */
    public function test_admin_can_create_a_new_event(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $waiter = $this->waiter();

        $eventDate = now()->addDays(10)->setTime(16, 0);
        $setupTime = now()->addDays(10)->setTime(11, 0);

        $response = $this->actingAs($admin)->post(route('events.store'), [
            'event_name' => '60th Birthday Celebration',
            'venue_address' => 'San Isidro, Nabua, Camarines Sur',
            'event_date' => $eventDate->format('Y-m-d H:i:s'),
            'setup_time' => $setupTime->format('Y-m-d H:i:s'),
            'guest_count' => 50,
            'notes' => 'Test booking',
            'waiters' => [
                ['id' => $waiter->id, 'role' => 'waiter'],
            ],
        ]);

        $event = Event::where('event_name', '60th Birthday Celebration')->first();

        $response->assertRedirect(route('events.show', $event));
        $this->assertNotNull($event);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'event_name' => '60th Birthday Celebration',
            'created_by' => $admin->id,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('event_waiter', [
            'event_id' => $event->id,
            'waiter_id' => $waiter->id,
            'confirmation_status' => 'pending',
            'role' => 'waiter',
        ]);
        Notification::assertSentTo($waiter->user, EventAssignedNotification::class);
    }

    /** TC-SCH-A02: Admin Edits an Existing Event */
    public function test_admin_can_edit_an_existing_event(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $event = $this->event($admin);

        $response = $this->actingAs($admin)->put(route('events.update', $event), [
            'event_name' => 'Updated Birthday Event',
            'venue_address' => 'San Jose, Iriga City',
            'event_date' => now()->addDays(20)->setTime(17, 0)->format('Y-m-d H:i:s'),
            'setup_time' => now()->addDays(20)->setTime(13, 0)->format('Y-m-d H:i:s'),
            'guest_count' => 100,
            'status' => 'scheduled',
            'notes' => 'Updated details',
            'waiters' => [],
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'event_name' => 'Updated Birthday Event',
            'guest_count' => 100,
            'venue_address' => 'San Jose, Iriga City',
        ]);
    }

    /** TC-SCH-A03: Admin Deletes an Event */
    public function test_admin_can_delete_an_event(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);

        $response = $this->actingAs($admin)->delete(route('events.destroy', $event));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** TC-SCH-A04: Admin Views Full Event Details */
    public function test_admin_can_view_full_event_details(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $event->waiters()->attach($waiter->id, [
            'confirmation_status' => 'pending',
            'role' => 'waiter',
        ]);

        $response = $this->actingAs($admin)->get(route('events.show', $event));

        $response->assertOk();
        $response->assertViewIs('events.show');
        $response->assertViewHas('event');
    }

    /** TC-SCH-A05: Admin Records Waiter Time In/Out */
    public function test_admin_can_record_waiter_time_in_and_time_out(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);

        $event->waiters()->attach($waiter->id, [
            'confirmation_status' => 'confirmed',
            'role' => 'waiter',
        ]);

        $timeIn = now()->setTime(13, 0, 0);
        $timeOut = now()->setTime(21, 0, 0);

        $event->waiters()->updateExistingPivot($waiter->id, [
            'time_in' => $timeIn,
            'time_out' => $timeOut,
        ]);

        $this->assertDatabaseHas('event_waiter', [
            'event_id' => $event->id,
            'waiter_id' => $waiter->id,
            'confirmation_status' => 'confirmed',
        ]);

        $pivot = $event->waiters()->where('waiter_id', $waiter->id)->first()->pivot;
        $this->assertNotNull($pivot->time_in);
        $this->assertNotNull($pivot->time_out);
    }

    /** TC-SCH-W01: Waiter Confirms Event Participation */
    public function test_waiter_can_confirm_event_participation(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $event->waiters()->attach($waiter->id, [
            'confirmation_status' => 'pending',
            'role' => 'waiter',
        ]);

        $response = $this->actingAs($waiter->user)->post(route('events.confirm', $event), [
            'status' => 'confirmed',
        ]);

        $response->assertSessionHas('success', 'Participation confirmed successfully.');
        $this->assertDatabaseHas('event_waiter', [
            'event_id' => $event->id,
            'waiter_id' => $waiter->id,
            'confirmation_status' => 'confirmed',
        ]);
        Notification::assertSentTo($admin, WaiterConfirmedNotification::class);
    }

    /** TC-SCH-W02: Waiter Declines Event Participation */
    public function test_waiter_can_decline_event_participation(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $event->waiters()->attach($waiter->id, [
            'confirmation_status' => 'pending',
            'role' => 'waiter',
        ]);

        $response = $this->actingAs($waiter->user)->post(route('events.confirm', $event), [
            'status' => 'declined',
        ]);

        $response->assertSessionHas('success', 'Participation declined. Admin has been notified.');
        $this->assertDatabaseHas('event_waiter', [
            'event_id' => $event->id,
            'waiter_id' => $waiter->id,
            'confirmation_status' => 'declined',
        ]);
        Notification::assertSentTo($admin, EventDeclinedNotification::class);
    }

    /** TC-SCH-W03: Waiter Views Their Own Event Details */
    public function test_waiter_can_view_an_assigned_event_details(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $event->waiters()->attach($waiter->id, [
            'confirmation_status' => 'confirmed',
            'role' => 'waiter',
        ]);

        $response = $this->actingAs($waiter->user)->get(route('waiters.my-event', $event));

        $response->assertOk();
        $response->assertViewIs('waiters.event-detail');
        $response->assertViewHas('event');
    }

    /** TC-SCH-W04: Waiter Cannot Access Admin Event Pages */
    public function test_waiter_cannot_access_admin_event_pages(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)->get(route('events.index'));

        $response->assertForbidden();
    }
}
