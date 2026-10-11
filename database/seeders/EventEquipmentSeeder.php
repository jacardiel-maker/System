<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventEquipment;
use App\Models\EquipmentItem;
use App\Models\Waiter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $mark = Waiter::whereHas('user', function ($query) {
            $query->where('email', 'markanthony@gmail.com');
        })->firstOrFail();

        $equipmentRequirements = [

            100 => [
                'Juice Dispenser' => 2,
                'Silver Spoon and Fork' => 100,
                'Serving Spoon' => 4,
                'Serving Fork' => 1,
                'Serving Tong' => 1,
                'Water Jug' => 2,
                'Round Dinner Plate' => 100,
                'Highball Glass' => 100,
                'Pitcher' => 2,
                'Chafing Dish' => 6,
                'Extension' => 1,
                'Cooler / Styro' => 3,
            ],

            50 => [
                'Juice Dispenser' => 2,
                'Silver Spoon and Fork' => 50,
                'Serving Spoon' => 3,
                'Serving Fork' => 1,
                'Serving Tong' => 1,
                'Water Jug' => 2,
                'Round Dinner Plate' => 50,
                'Highball Glass' => 50,
                'Pitcher' => 2,
                'Chafing Dish' => 5,
                'Extension' => 1,
                'Cooler / Styro' => 3,
            ],
        ];

        $events = Event::orderBy('event_date')->get();

        foreach ($events as $event) {

            // Check if Mark is assigned to this event
            $isAssigned = $event->waiters()
                ->where('waiters.id', $mark->id)
                ->exists();

            if (!$isAssigned) {
                continue;
            }

            $requirements = $equipmentRequirements[$event->guest_count] ?? [];

            foreach ($requirements as $equipmentName => $quantity) {

                $equipment = EquipmentItem::where(
                    'name',
                    $equipmentName
                )->firstOrFail();

                if ($equipment->total_quantity < $quantity) {
                    throw new \Exception(
                        "Not enough {$equipmentName} stock for {$event->name}."
                    );
                }

                EventEquipment::create([
                    'event_id' => $event->id,
                    'equipment_item_id' => $equipment->id,
                    'assigned_waiter_id' => $mark->id,
                    'quantity_allocated' => $quantity,
                    'quantity_returned' => $quantity,
                    'quantity_damaged' => 0,
                    'quantity_missing' => 0,
                    'status' => 'returned',
                    'notes' => 'Equipment returned in good condition.',
                    'returned_at' => $event->event_date,
                ]);
            }
        }

        $this->command->info(
            'Created equipment allocations for Mark Anthony Bebonia only.'
        );
    }
}