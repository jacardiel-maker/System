<?php

namespace Database\Seeders;

use App\Models\EquipmentLog;
use App\Models\EventEquipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EquipmentLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user who will be recorded as the logger
        $admin = User::where('email', 'eserveadmin@gmail.com')->firstOrFail();

        // Get all equipment allocations
        $allocations = EventEquipment::with([
            'event',
            'equipmentItem'
        ])->get();

        foreach ($allocations as $allocation) {

            // 1. Allocation Log
            EquipmentLog::create([
                'event_equipment_id' => $allocation->id,
                'logged_by' => $admin->id,
                'action' => 'allocated',
                'quantity' => $allocation->quantity_allocated,
                'remarks' => 'Equipment allocated for the event.',
                'created_at' => $allocation->created_at,
                'updated_at' => $allocation->created_at,
            ]);

            // 2. Return Log
            if ($allocation->quantity_returned > 0) {

                EquipmentLog::create([
                    'event_equipment_id' => $allocation->id,
                    'logged_by' => $admin->id,
                    'action' => 'returned',
                    'quantity' => $allocation->quantity_returned,
                    'remarks' => 'Equipment returned in good condition.',
                    'created_at' => $allocation->returned_at ?? $allocation->updated_at,
                    'updated_at' => $allocation->returned_at ?? $allocation->updated_at,
                ]);
            }
        }

        $this->command->info(
            'Created equipment allocation and return activity logs.'
        );
    }
}