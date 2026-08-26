<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EquipmentItem;
use App\Models\EventEquipment;
use App\Models\EquipmentLog;
use Illuminate\Database\Seeder;

class EventEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::with('waiters')->get();
        $items = EquipmentItem::all();
        $adminId = \App\Models\User::where('role', 'admin')->first()->id ?? 1;

        $bar = $this->command->getOutput()->createProgressBar($events->count());
        $bar->start();

        foreach ($events as $event) {
            if ($event->waiters->isEmpty() || $event->status === 'cancelled') {
                $bar->advance();
                continue;
            }

            $numItems = rand(1, 5);
            $selectedItems = $items->random(min($numItems, $items->count()));

            foreach ($selectedItems as $item) {
                // Refresh to get latest available_quantity from DB
                $item->refresh();

                $waiter = $event->waiters->random();

                // Allocate smaller amounts (5-30 max, respecting 20% of stock per event)
                $maxAllowed = min($item->available_quantity, max(5, intval($item->total_quantity * 0.15)), 30);
                if ($maxAllowed <= 0) continue;

                $qtyAllocated = rand(5, $maxAllowed);

                if ($event->status === 'completed') {
                    $status = $this->randomReturnStatus();
                } elseif ($event->status === 'ongoing') {
                    $status = 'in_use';
                } else {
                    $status = 'allocated';
                }

                $qtyReturned = 0;
                $qtyDamaged = 0;
                $qtyMissing = 0;

                if ($status === 'returned') {
                    $qtyReturned = $qtyAllocated;
                } elseif ($status === 'partially_returned') {
                    $qtyReturned = rand(1, $qtyAllocated - 1);
                    $qtyDamaged = rand(0, $qtyAllocated - $qtyReturned);
                    $qtyMissing = $qtyAllocated - $qtyReturned - $qtyDamaged;
                } elseif ($status === 'lost') {
                    $qtyMissing = $qtyAllocated;
                }

                $allocation = EventEquipment::create([
                    'event_id' => $event->id,
                    'equipment_item_id' => $item->id,
                    'assigned_waiter_id' => $waiter->id,
                    'quantity_allocated' => $qtyAllocated,
                    'quantity_returned' => $qtyReturned,
                    'quantity_damaged' => $qtyDamaged,
                    'quantity_missing' => $qtyMissing,
                    'status' => $status,
                    'notes' => $status !== 'allocated' ? 'Recorded via seed data' : null,
                    'returned_at' => in_array($status, ['returned', 'partially_returned', 'lost']) ? $event->event_date->addDays(rand(1, 3)) : null,
                ]);

                $item->decrement('available_quantity', $qtyAllocated);

                EquipmentLog::create([
                    'event_equipment_id' => $allocation->id,
                    'logged_by' => $adminId,
                    'action' => 'allocated',
                    'quantity' => $qtyAllocated,
                    'remarks' => 'Initial allocation to ' . $waiter->user->name,
                ]);

                if ($qtyReturned > 0) {
                    EquipmentLog::create([
                        'event_equipment_id' => $allocation->id,
                        'logged_by' => $adminId,
                        'action' => 'returned',
                        'quantity' => $qtyReturned,
                        'remarks' => 'Good condition return',
                    ]);
                }
                if ($qtyDamaged > 0) {
                    EquipmentLog::create([
                        'event_equipment_id' => $allocation->id,
                        'logged_by' => $adminId,
                        'action' => 'damaged',
                        'quantity' => $qtyDamaged,
                        'remarks' => 'Damaged during event',
                    ]);
                }
                if ($qtyMissing > 0) {
                    EquipmentLog::create([
                        'event_equipment_id' => $allocation->id,
                        'logged_by' => $adminId,
                        'action' => 'missing',
                        'quantity' => $qtyMissing,
                        'remarks' => 'Not returned by waiter',
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Created equipment allocations. Some stock remains available.');
    }

    private function randomReturnStatus(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 60) return 'returned';
        if ($rand <= 85) return 'partially_returned';
        if ($rand <= 95) return 'lost';
        return 'in_use';
    }
}