<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Waiter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WaiterSeeder extends Seeder
{
    public function run(): void
    {
        $waiters = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan@stephscatering.com', 'phone' => '09171234567', 'emergency' => '09181234567', 'address' => 'Brgy. Concepcion, Naga City, Camarines Sur'],
            ['name' => 'Maria Santos', 'email' => 'maria@stephscatering.com', 'phone' => '09172345678', 'emergency' => '09182345678', 'address' => 'Brgy. San Felipe, Naga City, Camarines Sur'],
            ['name' => 'Pedro Reyes', 'email' => 'pedro@stephscatering.com', 'phone' => '09173456789', 'emergency' => '09183456789', 'address' => 'Brgy. Dinaga, Naga City, Camarines Sur'],
            ['name' => 'Ana Gonzaga', 'email' => 'ana@stephscatering.com', 'phone' => '09174567890', 'emergency' => '09184567890', 'address' => 'Brgy. San Francisco, Iriga City, Camarines Sur'],
            ['name' => 'Carlo Bautista', 'email' => 'carlo@stephscatering.com', 'phone' => '09175678901', 'emergency' => '09185678901', 'address' => 'Brgy. San Juan, Pili, Camarines Sur'],
            ['name' => 'Liza Mendoza', 'email' => 'liza@stephscatering.com', 'phone' => '09176789012', 'emergency' => '09186789012', 'address' => 'Brgy. La Purisima, Nabua, Camarines Sur'],
            ['name' => 'Miguel Torres', 'email' => 'miguel@stephscatering.com', 'phone' => '09177890123', 'emergency' => '09187890123', 'address' => 'Brgy. San Jose, Baao, Camarines Sur'],
            ['name' => 'Grace Villanueva', 'email' => 'grace@stephscatering.com', 'phone' => '09178901234', 'emergency' => '09188901234', 'address' => 'Brgy. Sagrada, Bula, Camarines Sur'],
            ['name' => 'Ramon Castillo', 'email' => 'ramon@stephscatering.com', 'phone' => '09179012345', 'emergency' => '09189012345', 'address' => 'Brgy. San Lucas, Calabanga, Camarines Sur'],
            ['name' => 'Diana Fernandez', 'email' => 'diana@stephscatering.com', 'phone' => '09170123456', 'emergency' => '09180123456', 'address' => 'Brgy. Bahay, Libmanan, Camarines Sur'],
        ];

        foreach ($waiters as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('stephscatering'),
                'role' => 'waiter',
            ]);

            Waiter::create([
                'user_id' => $user->id,
                'phone' => $data['phone'],
                'emergency_contact' => $data['emergency'],
                'address' => $data['address'],
                'date_hired' => now()->subMonths(rand(1, 24))->subDays(rand(1, 30)),
                'status' => 'active',
            ]);
        }

        $this->command->info('Created 10 waiters. Default password: stephscatering');
    }
}