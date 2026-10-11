<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use Illuminate\Database\Seeder;

class EquipmentItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // PLATES
            [
                'category' => 'PLT',
                'name' => 'Round Dinner Plate',
                'serial_code' => 'PLT-001',
                'total' => 500,
                'condition' => 'good',
            ],
            [
                'category' => 'PLT',
                'name' => 'Dessert Plate',
                'serial_code' => 'PLT-002',
                'total' => 100,
                'condition' => 'good',
            ],
            [
                'category' => 'PLT',
                'name' => 'Serving Platter',
                'serial_code' => 'PLT-003',
                'total' => 50,
                'condition' => 'good',
            ],

            // UTENSILS
            [
                'category' => 'UTL',
                'name' => 'Serving Spoon',
                'serial_code' => 'UTL-001',
                'total' => 25,
                'condition' => 'good',
            ],
            [
                'category' => 'UTL',
                'name' => 'Serving Fork',
                'serial_code' => 'UTL-002',
                'total' => 10,
                'condition' => 'good',
            ],
            [
                'category' => 'UTL',
                'name' => 'Serving Tong',
                'serial_code' => 'UTL-003',
                'total' => 10,
                'condition' => 'good',
            ],
            [
                'category' => 'UTL',
                'name' => 'Gold Spoon and Fork',
                'serial_code' => 'UTL-004',
                'total' => 100,
                'condition' => 'good',
            ],
            [
                'category' => 'UTL',
                'name' => 'Silver Spoon and Fork',
                'serial_code' => 'UTL-005',
                'total' => 500,
                'condition' => 'good',
            ],

            // GLASSWARE
            [
                'category' => 'GLS',
                'name' => 'Highball Glass',
                'serial_code' => 'GLS-001',
                'total' => 500,
                'condition' => 'good',
            ],
            [
                'category' => 'GLS',
                'name' => 'Wine Glass',
                'serial_code' => 'GLS-002',
                'total' => 100,
                'condition' => 'good',
            ],
            [
                'category' => 'GLS',
                'name' => 'Water Glass',
                'serial_code' => 'GLS-003',
                'total' => 100,
                'condition' => 'good',
            ],

            // TRAYS
            [
                'category' => 'TRY',
                'name' => 'Bar Tray',
                'serial_code' => 'TRY-001',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'TRY',
                'name' => 'Serving Tray',
                'serial_code' => 'TRY-002',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'TRY',
                'name' => 'Dessert Rack',
                'serial_code' => 'TRY-003',
                'total' => 10,
                'condition' => 'good',
            ],

            // COOKING TOOLS
            [
                'category' => 'CKT',
                'name' => 'Food Warmer',
                'serial_code' => 'CKT-001',
                'total' => 10,
                'condition' => 'good',
            ],
            [
                'category' => 'CKT',
                'name' => 'Chafing Dish',
                'serial_code' => 'CKT-002',
                'total' => 35,
                'condition' => 'good',
            ],
            [
                'category' => 'CKT',
                'name' => 'Food Warmer Stand',
                'serial_code' => 'CKT-003',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'CKT',
                'name' => 'Burner',
                'serial_code' => 'CKT-004',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'CKT',
                'name' => 'Rice Cooker',
                'serial_code' => 'CKT-005',
                'total' => 10,
                'condition' => 'good',
            ],

            // TABLES AND CHAIRS
            [
                'category' => 'TCH',
                'name' => 'Banquet Table',
                'serial_code' => 'TCH-001',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'TCH',
                'name' => 'Banquet Chair',
                'serial_code' => 'TCH-002',
                'total' => 150,
                'condition' => 'good',
            ],
            [
                'category' => 'TCH',
                'name' => 'Buffet Table',
                'serial_code' => 'TCH-003',
                'total' => 10,
                'condition' => 'good',
            ],

            // LINENS AND COVERS
            [
                'category' => 'LNC',
                'name' => 'White Tablecloth',
                'serial_code' => 'LNC-001',
                'total' => 30,
                'condition' => 'good',
            ],
            [
                'category' => 'LNC',
                'name' => 'Skirting Cloth',
                'serial_code' => 'LNC-002',
                'total' => 30,
                'condition' => 'good',
            ],
            [
                'category' => 'LNC',
                'name' => 'Chair Cloth Cover',
                'serial_code' => 'LNC-003',
                'total' => 150,
                'condition' => 'good',
            ],

            // DECORATIONS
            [
                'category' => 'DEC',
                'name' => 'Centerpiece',
                'serial_code' => 'DEC-001',
                'total' => 20,
                'condition' => 'good',
            ],
            [
                'category' => 'DEC',
                'name' => 'Table Lamp',
                'serial_code' => 'DEC-002',
                'total' => 20,
                'condition' => 'good',
            ],

            // SERVING EQUIPMENT
            [
                'category' => 'SVE',
                'name' => 'Juice Dispenser',
                'serial_code' => 'SVE-001',
                'total' => 15,
                'condition' => 'good',
            ],
            [
                'category' => 'SVE',
                'name' => 'Juice Tower',
                'serial_code' => 'SVE-002',
                'total' => 10,
                'condition' => 'good',
            ],
            [
                'category' => 'SVE',
                'name' => 'Water Jug',
                'serial_code' => 'SVE-003',
                'total' => 15,
                'condition' => 'good',
            ],
            [
                'category' => 'SVE',
                'name' => 'Pitcher',
                'serial_code' => 'SVE-004',
                'total' => 15,
                'condition' => 'good',
            ],
            [
                'category' => 'SVE',
                'name' => 'Extension',
                'serial_code' => 'SVE-005',
                'total' => 10,
                'condition' => 'good',
            ],

            // COOLING EQUIPMENT
            [
                'category' => 'CLG',
                'name' => 'Ice Cooler',
                'serial_code' => 'CLG-001',
                'total' => 10,
                'condition' => 'good',
            ],
            [
                'category' => 'CLG',
                'name' => 'Cooler / Styro',
                'serial_code' => 'CLG-002',
                'total' => 20,
                'condition' => 'good',
            ],

            // STAFF UNIFORMS
            [
                'category' => 'UNI',
                'name' => 'Waiter Uniform',
                'serial_code' => 'UNI-001',
                'total' => 15,
                'condition' => 'good',
            ],
        ];

        foreach ($items as $item) {

            $category = EquipmentCategory::where(
                'code',
                $item['category']
            )->firstOrFail();

            EquipmentItem::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'serial_code' => $item['serial_code'],
                'total_quantity' => $item['total'],
                'available_quantity' => $item['total'],
                'condition' => $item['condition'],
            ]);
        }

        $this->command->info('Created equipment items.');
    }
}