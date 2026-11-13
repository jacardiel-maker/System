<?php

namespace Database\Seeders;

use App\Models\EquipmentLog;
use App\Models\EventEquipment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Makes the equipment logs match the allocations: every allocation gets an "allocated" log,
 * and whatever was returned / damaged / missing gets its own log, dated when it happened.
 *
 * Safe to run again at any time (php artisan db:seed --class=EquipmentLogSeeder):
 * it only adds the logs that are missing and never touches existing ones.
 */
class EquipmentLogSeeder extends Seeder
{
    public function run(): void
    {
        // The admin is recorded as the one who logged the activity
        $admin = User::where('email', 'eserveadmin@gmail.com')->first()
            ?? User::where('role', 'admin')->firstOrFail();

        $added = 0;

        EventEquipment::with('assignedWaiter.user')->orderBy('id')->chunkById(500, function ($allocations) use ($admin, &$added) {
            // allocation id => [action => quantity already logged]
            $logged = EquipmentLog::whereIn('event_equipment_id', $allocations->pluck('id'))
                ->selectRaw('event_equipment_id, action, SUM(quantity) as qty')
                ->groupBy('event_equipment_id', 'action')
                ->get()
                ->groupBy('event_equipment_id')
                ->map(fn ($rows) => $rows->pluck('qty', 'action'));

            $rows = [];

            foreach ($allocations as $allocation) {
                $have = $logged[$allocation->id] ?? collect();
                $allocatedAt = $allocation->created_at ?? now();
                $returnedAt = $allocation->returned_at ?? $allocation->updated_at ?? $allocatedAt;
                $waiterName = $allocation->assignedWaiter->user->name ?? null;

                // 1. Allocation log
                if (!$have->has('allocated')) {
                    $rows[] = $this->row($allocation->id, $admin->id, 'allocated', (int) $allocation->quantity_allocated,
                        $waiterName ? 'Allocated to ' . $waiterName : 'Equipment allocated for the event.', $allocatedAt);
                }

                // 2. Return / damaged / missing logs (only the part that is not logged yet)
                $outcomes = [
                    'returned' => [(int) $allocation->quantity_returned, $allocation->notes ?: 'Equipment returned in good condition.'],
                    'damaged' => [(int) $allocation->quantity_damaged, $allocation->notes ?: 'Damaged on return'],
                    'missing' => [(int) $allocation->quantity_missing, $allocation->notes ?: 'Not returned'],
                ];
                foreach ($outcomes as $action => [$quantity, $remarks]) {
                    $missing = $quantity - (int) ($have[$action] ?? 0);
                    if ($missing > 0) {
                        $rows[] = $this->row($allocation->id, $admin->id, $action, $missing, $remarks, $returnedAt);
                    }
                }
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                EquipmentLog::insert($chunk);
            }
            $added += count($rows);
        });

        $this->command->info("Equipment logs matched to the allocations: {$added} missing log(s) added.");
    }

    private function row(int $allocationId, int $adminId, string $action, int $quantity, string $remarks, $at): array
    {
        return [
            'event_equipment_id' => $allocationId,
            'logged_by' => $adminId,
            'action' => $action,
            'quantity' => $quantity,
            'remarks' => $remarks,
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
