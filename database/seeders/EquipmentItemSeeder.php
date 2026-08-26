<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use Illuminate\Database\Seeder;

class EquipmentItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = EquipmentCategory::all();

        if ($categories->isEmpty()) {
            $this->command->error('No equipment categories found. Run DatabaseSeeder first.');
            return;
        }

        $items = [
            // Plates (Category 1) - High stock
            ['name' => 'Dinner Plate 10"', 'serial_code' => 'PLT-001', 'total' => 2000, 'condition' => 'good'],
            ['name' => 'Dessert Plate 7"', 'serial_code' => 'PLT-002', 'total' => 1500, 'condition' => 'good'],
            ['name' => 'Soup Plate Deep', 'serial_code' => 'PLT-003', 'total' => 1200, 'condition' => 'fair'],
            ['name' => 'Serving Platter Large', 'serial_code' => 'PLT-004', 'total' => 300, 'condition' => 'good'],
            ['name' => 'Appetizer Plate Small', 'serial_code' => 'PLT-005', 'total' => 1800, 'condition' => 'new'],

            // Utensils (Category 2) - High stock
            ['name' => 'Dinner Fork', 'serial_code' => 'UTL-001', 'total' => 2500, 'condition' => 'good'],
            ['name' => 'Dinner Spoon', 'serial_code' => 'UTL-002', 'total' => 2500, 'condition' => 'good'],
            ['name' => 'Steak Knife', 'serial_code' => 'UTL-003', 'total' => 1500, 'condition' => 'fair'],
            ['name' => 'Serving Spoon Large', 'serial_code' => 'UTL-004', 'total' => 200, 'condition' => 'good'],
            ['name' => 'Salad Fork', 'serial_code' => 'UTL-005', 'total' => 1200, 'condition' => 'new'],

            // Glassware (Category 3) - High stock
            ['name' => 'Water Glass Tall', 'serial_code' => 'GLS-001', 'total' => 2000, 'condition' => 'good'],
            ['name' => 'Wine Glass Red', 'serial_code' => 'GLS-002', 'total' => 1200, 'condition' => 'good'],
            ['name' => 'Juice Glass Small', 'serial_code' => 'GLS-003', 'total' => 1500, 'condition' => 'fair'],
            ['name' => 'Champagne Flute', 'serial_code' => 'GLS-004', 'total' => 800, 'condition' => 'new'],
            ['name' => 'Mug Ceramic', 'serial_code' => 'GLS-005', 'total' => 600, 'condition' => 'good'],

            // Trays (Category 4) - Medium stock
            ['name' => 'Silver Serving Tray', 'serial_code' => 'TRY-001', 'total' => 150, 'condition' => 'good'],
            ['name' => 'Buffet Tray Stainless', 'serial_code' => 'TRY-002', 'total' => 120, 'condition' => 'fair'],
            ['name' => 'Transport Tray with Lid', 'serial_code' => 'TRY-003', 'total' => 80, 'condition' => 'good'],
            ['name' => 'Round Cake Tray', 'serial_code' => 'TRY-004', 'total' => 100, 'condition' => 'new'],
            ['name' => 'Rectangular Display Tray', 'serial_code' => 'TRY-005', 'total' => 100, 'condition' => 'good'],

            // Cooking Tools (Category 5) - Medium stock
            ['name' => 'Stock Pot 20L', 'serial_code' => 'CKT-001', 'total' => 50, 'condition' => 'good'],
            ['name' => 'Frying Pan Non-stick', 'serial_code' => 'CKT-002', 'total' => 80, 'condition' => 'fair'],
            ['name' => 'Spatula Set Silicone', 'serial_code' => 'CKT-003', 'total' => 100, 'condition' => 'new'],
            ['name' => 'Ladle Large Stainless', 'serial_code' => 'CKT-004', 'total' => 100, 'condition' => 'good'],
            ['name' => 'Chafing Dish Set', 'serial_code' => 'CKT-005', 'total' => 60, 'condition' => 'good'],
        ];

        foreach ($items as $index => $item) {
            $categoryIndex = floor($index / 5);
            $category = $categories[$categoryIndex] ?? $categories[0];

            EquipmentItem::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'serial_code' => $item['serial_code'],
                'total_quantity' => $item['total'],
                'available_quantity' => $item['total'],
                'condition' => $item['condition'],
            ]);
        }

        $this->command->info('Created 25 equipment items with high stock levels.');
    }
}