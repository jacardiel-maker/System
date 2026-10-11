<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Philippine Address Data
        $this->call(AddressSeeder::class);

        // 2. Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'eserveadmin@gmail.com',
            'password' => Hash::make('eserveadmin'),
            'role' => 'admin',
        ]);

        // 3. Equipment Categories
        $categories = [
            [
                'name' => 'Plates',
                'code' => 'PLT',
                'description' => 'All types of serving plates',
            ],
            [
                'name' => 'Utensils',
                'code' => 'UTL',
                'description' => 'Forks, spoons, knives and serving utensils',
            ],
            [
                'name' => 'Glassware',
                'code' => 'GLS',
                'description' => 'Drinking glasses and cups',
            ],
            [
                'name' => 'Trays',
                'code' => 'TRY',
                'description' => 'Serving and transport trays',
            ],
            [
                'name' => 'Cooking Tools',
                'code' => 'CKT',
                'description' => 'Cooking and food preparation equipment',
            ],
            [
                'name' => 'Tables and Chairs',
                'code' => 'TCH',
                'description' => 'Tables, chairs and related furniture',
            ],
            [
                'name' => 'Linens and Covers',
                'code' => 'LNC',
                'description' => 'Tablecloths, chair covers and skirting cloths',
            ],
            [
                'name' => 'Decorations',
                'code' => 'DEC',
                'description' => 'Centerpieces, lamps and event decorations',
            ],
            [
                'name' => 'Serving Equipment',
                'code' => 'SVE',
                'description' => 'Food warmers, dispensers and other serving equipment',
            ],
            [
                'name' => 'Cooling Equipment',
                'code' => 'CLG',
                'description' => 'Ice coolers and other cooling equipment',
            ],
            [
                'name' => 'Staff Uniforms',
                'code' => 'UNI',
                'description' => 'Uniforms and attire for waiters and servers',
            ],
        ];

        foreach ($categories as $category) {
            EquipmentCategory::create($category);
        }

        // 4. Additional Seeders
        $this->call([
            EquipmentItemSeeder::class,
            WaiterSeeder::class,
            EventSeeder::class,
            EventEquipmentSeeder::class,
        ]);

        $this->command->info('=================================================');
        $this->command->info('All seeders completed successfully!');
        $this->command->info('Login: eserveadmin@gmail.com / eserveadmin');
        $this->command->info('=================================================');
    }
}