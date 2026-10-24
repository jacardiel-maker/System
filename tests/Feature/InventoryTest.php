<?php

namespace Tests\Feature;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use App\Models\User;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'name' => 'Inventory Admin',
            'role' => 'admin',
        ]);
    }

    private function waiter(): Waiter
    {
        $user = User::factory()->create([
            'name' => 'Inventory Waiter',
            'role' => 'waiter',
        ]);

        return Waiter::create([
            'user_id' => $user->id,
            'phone' => '09123456789',
            'status' => 'active',
        ]);
    }

    private function category(array $overrides = []): EquipmentCategory
    {
        return EquipmentCategory::create(array_merge([
            'name' => 'Plates',
            'code' => 'PLT' . fake()->unique()->numerify('###'),
            'description' => 'Serving plates',
        ], $overrides));
    }

    private function item(EquipmentCategory $category, array $overrides = []): EquipmentItem
    {
        return EquipmentItem::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Dinner Plate',
            'serial_code' => 'PLATE-' . fake()->unique()->numerify('#####'),
            'total_quantity' => 100,
            'available_quantity' => 100,
            'condition' => 'good',
        ], $overrides));
    }

    /** TC-INV-A01: Admin Creates an Equipment Category */
    public function test_admin_can_create_an_equipment_category(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('equipment-categories.store'), [
            'name' => 'Glassware',
            'code' => 'GLS',
            'description' => 'Drinking glasses and cups',
        ]);

        $response->assertRedirect(route('equipment-categories.index'));
        $this->assertDatabaseHas('equipment_categories', [
            'name' => 'Glassware',
            'code' => 'GLS',
        ]);
    }

    /** TC-INV-A02: Admin Edits an Equipment Category */
    public function test_admin_can_edit_an_equipment_category(): void
    {
        $admin = $this->admin();
        $category = $this->category();

        $response = $this->actingAs($admin)->put(route('equipment-categories.update', $category), [
            'name' => 'Updated Plates',
            'code' => $category->code,
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('equipment-categories.show', $category));
        $this->assertDatabaseHas('equipment_categories', [
            'id' => $category->id,
            'name' => 'Updated Plates',
            'description' => 'Updated description',
        ]);
    }

    /** TC-INV-A03: Admin Creates an Equipment Item */
    public function test_admin_can_create_an_equipment_item(): void
    {
        $admin = $this->admin();
        $category = $this->category();

        $response = $this->actingAs($admin)->post(route('equipment-items.store'), [
            'category_id' => $category->id,
            'name' => 'Serving Tray',
            'serial_code' => 'TRAY-001',
            'total_quantity' => 25,
            'condition' => 'good',
        ]);

        $response->assertRedirect(route('equipment-items.index'));
        $this->assertDatabaseHas('equipment_items', [
            'category_id' => $category->id,
            'name' => 'Serving Tray',
            'total_quantity' => 25,
            'available_quantity' => 25,
        ]);
    }

    /** TC-INV-A04: Admin Edits an Equipment Item */
    public function test_admin_can_edit_an_equipment_item(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $item = $this->item($category, ['total_quantity' => 50, 'available_quantity' => 50]);

        $response = $this->actingAs($admin)->put(route('equipment-items.update', $item), [
            'category_id' => $category->id,
            'name' => 'Updated Dinner Plate',
            'serial_code' => $item->serial_code,
            'total_quantity' => 75,
            'condition' => 'fair',
        ]);

        $response->assertRedirect(route('equipment-items.show', $item));
        $this->assertDatabaseHas('equipment_items', [
            'id' => $item->id,
            'name' => 'Updated Dinner Plate',
            'total_quantity' => 75,
            'available_quantity' => 75,
            'condition' => 'fair',
        ]);
    }

    /** TC-INV-A05: Admin Deletes an Equipment Item */
    public function test_admin_can_delete_an_equipment_item_without_allocation_history(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $item = $this->item($category);

        $response = $this->actingAs($admin)->delete(route('equipment-items.destroy', $item));

        $response->assertRedirect(route('equipment-items.index'));
        $this->assertDatabaseMissing('equipment_items', ['id' => $item->id]);
    }

    /** TC-INV-A06: Admin Views Inventory */
    public function test_admin_can_view_inventory_items(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $item = $this->item($category);

        $response = $this->actingAs($admin)->get(route('equipment-items.index'));

        $response->assertOk();
        $response->assertViewIs('equipment-items.index');
        $response->assertViewHas('items');
        $response->assertSee($item->name);
    }

    /** TC-INV-A07: Low-Stock Warning */
    public function test_inventory_low_stock_filter_identifies_items_below_twenty_percent(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $lowStock = $this->item($category, [
            'name' => 'Low Stock Plates',
            'total_quantity' => 100,
            'available_quantity' => 15,
        ]);
        // Exactly 20% is not low stock (the rule is strictly below 20%)
        $borderStock = $this->item($category, [
            'name' => 'Border Stock Plates',
            'total_quantity' => 100,
            'available_quantity' => 20,
        ]);
        $normalStock = $this->item($category, [
            'name' => 'Normal Stock Plates',
            'total_quantity' => 100,
            'available_quantity' => 50,
        ]);

        $response = $this->actingAs($admin)->get(route('equipment-items.index', ['stock' => 'low']));

        $response->assertOk();
        $response->assertSee($lowStock->name);
        $response->assertDontSee($borderStock->name);
        $response->assertDontSee($normalStock->name);
    }

    /** TC-INV-W01: Waiter Cannot Access Inventory */
    public function test_waiter_cannot_access_inventory_pages(): void
    {
        $waiter = $this->waiter();

        $response = $this->actingAs($waiter->user)->get(route('equipment-items.index'));

        $response->assertForbidden();
    }

    /** TC-INV-W02: Waiter Cannot Modify Inventory */
    public function test_waiter_cannot_create_inventory_items(): void
    {
        $waiter = $this->waiter();
        $category = $this->category();

        $response = $this->actingAs($waiter->user)->post(route('equipment-items.store'), [
            'category_id' => $category->id,
            'name' => 'Unauthorized Item',
            'serial_code' => 'UNAUTH-001',
            'total_quantity' => 10,
            'condition' => 'good',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('equipment_items', [
            'name' => 'Unauthorized Item',
        ]);
    }
}
