<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Waiter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', 'eserveadmin@gmail.com')->firstOrFail();

        $events = [

            [
                'event_name' => 'Burial',
                'venue_address' => 'San Juan, Baao',
                'event_date' => '2026-08-11 12:00:00',
                'setup_time' => '2026-08-11 08:00:00',
                'guest_count' => 100,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'jamescedie@gmail.com',
                    'sergio@gmail.com',
                ],
            ],

            [
                'event_name' => '60th Birthday Celebration',
                'venue_address' => 'San Isidro, Nabua',
                'event_date' => '2026-08-21 16:00:00',
                'setup_time' => '2026-08-21 11:00:00',
                'guest_count' => 50,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'jamescedie@gmail.com',
                    'sergio@gmail.com',
                    'janbenjie@gmail.com',
                ],
            ],

            [
                'event_name' => '1st Birthday Celebration',
                'venue_address' => 'San Miguel, Nabua',
                'event_date' => '2026-08-24 19:00:00',
                'setup_time' => '2026-08-24 17:00:00',
                'guest_count' => 50,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'sergio@gmail.com',
                    'ian@gmail.com',
                ],
            ],

            [
                'event_name' => 'Death Anniversary',
                'venue_address' => 'San Juan, Iriga',
                'event_date' => '2026-08-25 16:00:00',
                'setup_time' => '2026-08-25 15:00:00',
                'guest_count' => 50,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'sergio@gmail.com',
                ],
            ],

            [
                'event_name' => 'Death Anniversary',
                'venue_address' => 'San Isidro, Nabua',
                'event_date' => '2026-09-01 15:00:00',
                'setup_time' => '2026-09-01 11:00:00',
                'guest_count' => 50,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'sergio@gmail.com',
                ],
            ],

            [
                'event_name' => 'Welcome Celebration',
                'venue_address' => 'Caranday, Baao',
                'event_date' => '2026-08-10 17:00:00',
                'setup_time' => '2026-08-10 08:00:00',
                'guest_count' => 100,
                'status' => 'completed',
                'notes' => null,
                'waiters' => [
                    'markanthony@gmail.com',
                    'jamescedie@gmail.com',
                    'sergio@gmail.com',
                ],
            ],
        ];

        foreach ($events as $data) {

            // Create event
            $event = Event::create([
                'event_name' => $data['event_name'],
                'venue_address' => $data['venue_address'],
                'event_date' => $data['event_date'],
                'setup_time' => $data['setup_time'],
                'guest_count' => $data['guest_count'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'created_by' => $admin->id,
            ]);

            // Assign waiters
            foreach ($data['waiters'] as $email) {

                $waiter = Waiter::whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                })->firstOrFail();

                $event->waiters()->attach($waiter->id, [
                    'confirmation_status' => 'confirmed',
                    'role' => 'waiter',
                    'confirmed_at' => Carbon::parse(
                        $data['event_date']
                    )->subDays(3),
                ]);
            }
        }

        $this->command->info(
            'Created 6 events with exact waiter assignments.'
        );
    }
}