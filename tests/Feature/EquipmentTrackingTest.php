<?php

namespace Tests\Feature;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use App\Models\EquipmentLog;
use App\Models\Event;
use App\Models\EventEquipment;
use App\Models\User;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name' => 'Equipment Admin',
            'role' => 'admin',
        ]);
    }

    private function waiter(): Waiter
    {
        $user = User::factory()->create([
            'name' => 'Equipment Waiter',
            'role' => 'waiter',
        ]);

        return Waiter::create([
            'user_id' => $user->id,
            'phone' => '09123456789',
            'status' => 'active',
        ]);
    }

    private function category(): EquipmentCategory
    {
        return EquipmentCategory::create([
            'name' => 'Plates',
            'code' => 'PLT' . fake()->unique()->numerify('###'),
            'description' => 'Serving plates',
        ]);
    }

    private function item(EquipmentCategory $category, int $quantity = 100): EquipmentItem
    {
        return EquipmentItem::create([
            'category_id' => $category->id,
            'name' => 'Dinner Plate',
            'serial_code' => 'PLATE-' . fake()->unique()->numerify('#####'),
            'total_quantity' => $quantity,
            'available_quantity' => $quantity,
            'condition' => 'good',
        ]);
    }

    private function event(User $admin): Event
    {
        return Event::create([
            'event_name' => 'Equipment Test Event',
            'venue_address' => 'San Miguel, Nabua, Camarines Sur',
            'event_date' => now()->addDays(14),
            'setup_time' => now()->addDays(14)->setTime(11, 0),
            'guest_count' => 50,
            'status' => 'scheduled',
            'created_by' => $admin->id,
        ]);
    }

    private function allocation(User $admin, Event $event, EquipmentItem $item, Waiter $waiter, int $quantity = 10): EventEquipment
    {
        $item->decrement('available_quantity', $quantity);

        return EventEquipment::create([
            'event_id' => $event->id,
            'equipment_item_id' => $item->id,
            'assigned_waiter_id' => $waiter->id,
            'quantity_allocated' => $quantity,
            'status' => 'allocated',
        ]);
    }

    /** TC-EQ-A01: Admin Allocates Equipment to an Event */
    public function test_admin_can_allocate_equipment_to_an_event(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);

        $response = $this->actingAs($admin)->post(route('event-equipment.store'), [
            'event_id' => $event->id,
            'assigned_waiter_id' => $waiter->id,
            'equipment' => [
                $item->id => [
                    'selected' => '1',
                    'quantity' => 10,
                ],
            ],
            'notes' => 'For birthday event',
        ]);

        $response->assertRedirect(route('event-equipment.index'));
        $this->assertDatabaseHas('event_equipment', [
            'event_id' => $event->id,
            'equipment_item_id' => $item->id,
            'assigned_waiter_id' => $waiter->id,
            'quantity_allocated' => 10,
            'status' => 'allocated',
        ]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 40,
        ]);
        $this->assertDatabaseHas('equipment_logs', [
            'action' => 'allocated',
            'quantity' => 10,
            'logged_by' => $admin->id,
        ]);
    }

    /** TC-EQ-A02: Allocation Cannot Exceed Available Stock */
    public function test_equipment_allocation_cannot_exceed_available_stock(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 5);

        $response = $this->actingAs($admin)->post(route('event-equipment.store'), [
            'event_id' => $event->id,
            'assigned_waiter_id' => $waiter->id,
            'equipment' => [
                $item->id => [
                    'selected' => '1',
                    'quantity' => 10,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('event_equipment', [
            'event_id' => $event->id,
            'equipment_item_id' => $item->id,
        ]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 5,
        ]);
    }

    /** TC-EQ-A03: Admin Edits an Equipment Allocation */
    public function test_admin_can_edit_an_equipment_allocation(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);
        $allocation = $this->allocation($admin, $event, $item, $waiter, 10);

        $response = $this->actingAs($admin)->put(route('event-equipment.update', $allocation), [
            'event_id' => $event->id,
            'equipment_item_id' => $item->id,
            'assigned_waiter_id' => $waiter->id,
            'quantity_allocated' => 15,
            'notes' => 'Updated quantity',
        ]);

        $response->assertRedirect(route('event-equipment.show', $allocation));
        $this->assertDatabaseHas('event_equipment', [
            'id' => $allocation->id,
            'quantity_allocated' => 15,
            'notes' => 'Updated quantity',
        ]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 35,
        ]);
    }

    /** TC-EQ-A04: Admin Deletes an Equipment Allocation */
    public function test_admin_can_delete_an_active_equipment_allocation(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);
        $allocation = $this->allocation($admin, $event, $item, $waiter, 10);

        $response = $this->actingAs($admin)->delete(route('event-equipment.destroy', $allocation));

        $response->assertRedirect(route('event-equipment.index'));
        $this->assertDatabaseMissing('event_equipment', ['id' => $allocation->id]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 50,
        ]);
    }

    /** TC-EQ-A05: Admin Records Equipment Return */
    public function test_admin_can_record_equipment_return(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);
        $allocation = $this->allocation($admin, $event, $item, $waiter, 10);

        // Record Return keeps the admin on the page they submitted from
        $response = $this->actingAs($admin)
            ->from(route('event-equipment.show', $allocation))
            ->post(route('event-equipment.return', $allocation), [
            'quantity_returned' => 10,
            'quantity_damaged' => 0,
            'quantity_missing' => 0,
            'notes' => 'All returned in good condition',
        ]);

        $response->assertRedirect(route('event-equipment.show', $allocation));
        $this->assertDatabaseHas('event_equipment', [
            'id' => $allocation->id,
            'quantity_returned' => 10,
            'quantity_damaged' => 0,
            'quantity_missing' => 0,
            'status' => 'returned',
        ]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 50,
        ]);
        $this->assertDatabaseHas('equipment_logs', [
            'event_equipment_id' => $allocation->id,
            'action' => 'returned',
            'quantity' => 10,
        ]);
    }

    /** TC-EQ-A06: Admin Records Damaged/Missing Equipment */
    public function test_admin_can_record_damaged_and_missing_equipment(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);
        $allocation = $this->allocation($admin, $event, $item, $waiter, 10);

        // Record Return keeps the admin on the page they submitted from
        $response = $this->actingAs($admin)
            ->from(route('event-equipment.show', $allocation))
            ->post(route('event-equipment.return', $allocation), [
            'quantity_returned' => 6,
            'quantity_damaged' => 2,
            'quantity_missing' => 2,
            'notes' => 'Two damaged and two missing',
        ]);

        $response->assertRedirect(route('event-equipment.show', $allocation));
        $this->assertDatabaseHas('event_equipment', [
            'id' => $allocation->id,
            'quantity_returned' => 6,
            'quantity_damaged' => 2,
            'quantity_missing' => 2,
            'status' => 'returned',
        ]);
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'available_quantity' => 46,
        ]);
        $this->assertDatabaseHas('equipment_logs', [
            'event_equipment_id' => $allocation->id,
            'action' => 'damaged',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('equipment_logs', [
            'event_equipment_id' => $allocation->id,
            'action' => 'missing',
            'quantity' => 2,
        ]);
    }

    /** TC-EQ-A07: Admin Views Equipment Logs */
    public function test_admin_can_view_equipment_logs(): void
    {
        $admin = $this->admin();
        $waiter = $this->waiter();
        $event = $this->event($admin);
        $category = $this->category();
        $item = $this->item($category, 50);
        $allocation = $this->allocation($admin, $event, $item, $waiter, 10);

        EquipmentLog::create([
            'event_equipment_id' => $allocation->id,
            'logged_by' => $admin->id,
            'action' => 'allocated',
            'quantity' => 10,
            'remarks' => 'Test equipment log',
        ]);

        $response = $this->actingAs($admin)->get(route('equipment-logs.index'));

        $response->assertOk();
        $response->assertViewIs('equipment-logs.index');
        $response->assertViewHas('logs');
        $response->assertSee('Equipment Test Event');
        $response->assertSee('Dinner Plate');
        $response->assertSee('Allocated');
        $response->assertSee('10');
    }

    /** TC-EQ-W01: Waiter Cannot Access Equipment Tracking */
    public function test_waiter_cannot_access_equipment_tracking_pages(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)->get(route('event-equipment.index'));

        $response->assertForbidden();
    }
}
