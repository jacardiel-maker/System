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
            'customer_name' => 'Test Customer',
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
            'customer_name' => 'Juan Dela Cruz',
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
            'customer_name' => 'Maria Santos',
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
            'customer_name' => 'Maria Santos',
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
            'decline_reason' => 'I have a family emergency.',
        ]);

        $response->assertSessionHas('success', 'Participation declined. The owner has been notified.');
        $this->assertDatabaseHas('event_waiter', [
            'event_id' => $event->id,
            'waiter_id' => $waiter->id,
            'confirmation_status' => 'declined',
            'decline_reason' => 'I have a family emergency.',
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

    /** Form data for creating / updating an event on the given day. */
    private function eventPayload(\Carbon\Carbon $day, array $overrides = []): array
    {
        return array_merge([
            'event_name' => 'Third Event',
            'customer_name' => 'Pedro Reyes',
            'venue_address' => 'Poblacion, Nabua, Camarines Sur',
            'event_date' => $day->copy()->setTime(18, 0)->format('Y-m-d H:i:s'),
            'setup_time' => $day->copy()->setTime(13, 0)->format('Y-m-d H:i:s'),
            'guest_count' => 80,
        ], $overrides);
    }

    /** TC-SCH-A06: Only 2 events can be scheduled per day */
    public function test_a_third_event_on_the_same_day_is_blocked(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $day = now()->addDays(30)->startOfDay();

        $this->event($admin, ['event_name' => 'Morning Event', 'event_date' => $day->copy()->setTime(9, 0), 'setup_time' => $day->copy()->setTime(6, 0)]);

        // Second event on that day is still allowed
        $this->actingAs($admin)->post(route('events.store'), $this->eventPayload($day, ['event_name' => 'Second Event']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', ['event_name' => 'Second Event']);

        // Third one is refused
        $this->actingAs($admin)->post(route('events.store'), $this->eventPayload($day))
            ->assertSessionHasErrors('event_date');
        $this->assertDatabaseMissing('events', ['event_name' => 'Third Event']);

        // The availability check used by the form and the calendar reports the day as full
        $this->actingAs($admin)->getJson(route('events.availability', ['date' => $day->toDateString()]))
            ->assertOk()
            ->assertJson(['full' => true, 'limit' => 2]);
    }

    /** TC-SCH-A07: A cancelled event does not take up one of the 2 slots */
    public function test_cancelled_events_do_not_count_toward_the_daily_limit(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $day = now()->addDays(31)->startOfDay();

        $this->event($admin, ['event_date' => $day->copy()->setTime(9, 0), 'setup_time' => $day->copy()->setTime(6, 0)]);
        $this->event($admin, ['event_date' => $day->copy()->setTime(14, 0), 'setup_time' => $day->copy()->setTime(10, 0), 'status' => 'cancelled']);

        $this->actingAs($admin)->post(route('events.store'), $this->eventPayload($day))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', ['event_name' => 'Third Event']);
    }

    /** TC-SCH-A08: An event cannot be moved to a day that is already full */
    public function test_an_event_cannot_be_moved_to_a_fully_booked_day(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $fullDay = now()->addDays(32)->startOfDay();
        $otherDay = now()->addDays(33)->startOfDay();

        $this->event($admin, ['event_date' => $fullDay->copy()->setTime(9, 0), 'setup_time' => $fullDay->copy()->setTime(6, 0)]);
        $this->event($admin, ['event_date' => $fullDay->copy()->setTime(15, 0), 'setup_time' => $fullDay->copy()->setTime(11, 0)]);
        $moving = $this->event($admin, ['event_name' => 'Moving Event', 'event_date' => $otherDay->copy()->setTime(15, 0), 'setup_time' => $otherDay->copy()->setTime(11, 0)]);

        $this->actingAs($admin)->put(route('events.update', $moving), $this->eventPayload($fullDay, ['event_name' => 'Moving Event', 'status' => 'scheduled']))
            ->assertSessionHasErrors('event_date');
        $this->assertSame($otherDay->toDateString(), $moving->fresh()->event_date->toDateString());

        // Editing an event without changing its day still works, even though that day is full
        $staying = Event::whereDate('event_date', $fullDay->toDateString())->first();
        $this->actingAs($admin)->put(route('events.update', $staying), $this->eventPayload($fullDay, ['event_name' => 'Renamed Event', 'status' => 'scheduled']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', ['id' => $staying->id, 'event_name' => 'Renamed Event']);
    }

    /** TC-SCH-A09: Setup must be scheduled before the event */
    public function test_setup_time_must_be_before_the_event(): void
    {
        $admin = $this->admin();
        $day = now()->addDays(34)->startOfDay();
        $event = $this->event($admin, ['event_date' => $day->copy()->setTime(15, 0), 'setup_time' => $day->copy()->setTime(11, 0)]);

        $late = ['setup_time' => $day->copy()->setTime(20, 0)->format('Y-m-d H:i:s')];

        $this->actingAs($admin)->post(route('events.store'), $this->eventPayload($day, $late))
            ->assertSessionHasErrors('setup_time');
        $this->actingAs($admin)->put(route('events.update', $event), $this->eventPayload($day, $late + ['status' => 'scheduled']))
            ->assertSessionHasErrors('setup_time');
    }

    /** TC-SCH-A10: An event cannot start until waiters and equipment are set up */
    public function test_event_cannot_be_marked_ongoing_before_waiters_and_equipment_are_set_up(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $waiter = $this->waiter();
        $day = now()->addDays(35)->startOfDay();
        $event = $this->event($admin, ['event_date' => $day->copy()->setTime(15, 0), 'setup_time' => $day->copy()->setTime(11, 0)]);

        $payload = $this->eventPayload($day, ['status' => 'ongoing', 'waiters' => [['id' => $waiter->id, 'role' => 'waiter']]]);

        // No equipment allocated yet
        $this->actingAs($admin)->put(route('events.update', $event), $payload)->assertSessionHasErrors('status');
        $this->assertSame('scheduled', $event->fresh()->status);

        $category = \App\Models\EquipmentCategory::create(['name' => 'Plates', 'code' => 'PLT']);
        $item = \App\Models\EquipmentItem::create([
            'category_id' => $category->id, 'name' => 'Dinner Plate', 'total_quantity' => 50, 'available_quantity' => 40, 'condition' => 'good',
        ]);
        \App\Models\EventEquipment::create([
            'event_id' => $event->id, 'equipment_item_id' => $item->id, 'assigned_waiter_id' => $waiter->id,
            'quantity_allocated' => 10, 'status' => 'allocated',
        ]);

        $this->actingAs($admin)->put(route('events.update', $event), $payload)->assertSessionHasNoErrors();
        $this->assertSame('ongoing', $event->fresh()->status);
    }

    /** TC-SCH-A11: Picking a day on the calendar opens the form with that day filled in */
    public function test_create_form_is_prefilled_with_the_day_picked_on_the_calendar(): void
    {
        $admin = $this->admin();
        $day = now()->addDays(36)->toDateString();

        $this->actingAs($admin)->get(route('events.create', ['date' => $day]))
            ->assertOk()
            ->assertSee('value="' . $day . 'T12:00"', false);
    }

    /** TC-SCH-A12: The waiter profile shows that waiter's own schedule calendar */
    public function test_waiter_profile_shows_an_individual_schedule_calendar(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter('Calendar Waiter');
        $event = $this->event($admin, ['event_name' => 'Calendar Wedding']);
        $event->waiters()->attach($waiter->id, ['role' => 'waiter', 'confirmation_status' => 'confirmed']);

        $this->actingAs($admin)->get(route('waiters.show', $waiter))
            ->assertOk()
            ->assertSee('id="waiterCalendar"', false)
            ->assertSee('Calendar Wedding');
    }
}
