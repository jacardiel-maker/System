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
            'name'     => 'Admin User',
            'email'    => 'admin@stephscatering.com',
            'password' => Hash::make('stephscatering'),
            'role'     => 'admin',
        ]);

        // 3. Equipment Categories
        $categories = [
            ['name' => 'Plates',        'code' => 'PLT', 'description' => 'All types of serving plates'],
            ['name' => 'Utensils',      'code' => 'UTL', 'description' => 'Forks, spoons, knives'],
            ['name' => 'Glassware',     'code' => 'GLS', 'description' => 'Drinking glasses and cups'],
            ['name' => 'Trays',         'code' => 'TRY', 'description' => 'Serving and transport trays'],
            ['name' => 'Cooking Tools', 'code' => 'CKT', 'description' => 'Pots, pans, and cooking utensils'],
        ];

        foreach ($categories as $cat) {
            EquipmentCategory::create($cat);
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
        $this->command->info('Login: admin@stephscatering.com / stephscatering');
        $this->command->info('=================================================');
    }
}