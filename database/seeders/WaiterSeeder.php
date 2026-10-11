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

            [
                'name' => 'Mark Anthony Bebonia',
                'email' => 'markanthony@gmail.com',
                'phone' => '09639385714279',
                'emergency' => '639385714279',
                'address' => 'San Esteban, Nabua, Camarines Sur, Bicol Region',
                'date_hired' => '2023-11-10',
            ],

            [
                'name' => 'James Cedie Sergio',
                'email' => 'jamescedie@gmail.com',
                'phone' => '09916246854',
                'emergency' => '09916246854',
                'address' => 'Masoli, Bato, Camarines Sur, Bicol Region',
                'date_hired' => '2025-08-11',
            ],

            [
                'name' => 'Sergio Sergio',
                'email' => 'sergio@gmail.com',
                'phone' => '09929691263',
                'emergency' => '09929391263',
                'address' => 'Masoli, Bato, Camarines Sur, Bicol Region',
                'date_hired' => '2025-12-16',
            ],

            [
                'name' => 'Ian Parma',
                'email' => 'ian@gmail.com',
                'phone' => '09918918720',
                'emergency' => '09918918720',
                'address' => 'Santa Lucia, Baras, Nabua, Camarines Sur, Bicol Region',
                'date_hired' => '2026-02-16',
            ],

            [
                'name' => 'Jan Benjie Cardiel',
                'email' => 'janbenjie@gmail.com',
                'phone' => '09916822804',
                'emergency' => '09151450351',
                'address' => 'Masoli, Bato, Camarines Sur, Bicol Region',
                'date_hired' => '2025-12-16',
            ],
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
                'date_hired' => $data['date_hired'],
                'status' => 'active',
            ]);
        }

        $this->command->info(
            'Created 5 waiters. Default password: stephscatering'
        );
    }
}