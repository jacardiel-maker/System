<?php

namespace Database\Seeders;

use App\Models\EquipmentItem;
use App\Models\EquipmentLog;
use App\Models\Event;
use App\Models\EventEquipment;
use App\Models\User;
use App\Models\Waiter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Bulk demo data: 20 waiters + 200 events, each with assigned waiters, equipment
 * allocated to those waiters, and the matching equipment logs.
 *
 * Run AFTER the main seeder (needs the admin account and equipment items):
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Stock follows the same rules as the app: allocating takes stock out, a return
 * puts back only the good units (damaged / missing stay out).
 */
class DemoDataSeeder extends Seeder
{
    private const EVENT_COUNT = 200;
    private const PAST_EVENTS = 192;   // completed
    private const TODAY_EVENTS = 2;    // ongoing
    // the rest (6) are upcoming within the next 2 weeks. Kept few on purpose: their
    // equipment is still out, and the inventory can only cover a handful of events at once.

    private const WAITER_PASSWORD = 'eserveadmin';

    private array $waiterNames = [
        'Rico Villanueva', 'Paolo Mendoza', 'Kevin Bautista', 'Joshua Ramos', 'Carlo Aquino',
        'Miguel Santos', 'Adrian Reyes', 'Christian Garcia', 'Jerome Navarro', 'Nathaniel Cruz',
        'Angelica Torres', 'Kristine Flores', 'Patricia Morales', 'Jasmine Castillo', 'Camille Rivera',
        'Bea Gonzales', 'Andrea Domingo', 'Nicole Pascual', 'Rhea Salazar', 'Joanna Delos Santos',
    ];

    private array $eventTypes = [
        ['Wedding Reception', 80, 150],
        ['Debut Celebration', 60, 120],
        ['Birthday Celebration', 30, 80],
        ['1st Birthday Celebration', 30, 70],
        ['60th Birthday Celebration', 40, 100],
        ['Christening', 30, 70],
        ['Death Anniversary', 30, 60],
        ['Burial', 50, 120],
        ['Wedding Anniversary', 40, 80],
        ['Graduation Party', 30, 70],
        ['Company Year-End Party', 60, 150],
        ['Fiesta Celebration', 50, 120],
        ['Family Reunion', 40, 100],
        ['Baptismal Reception', 30, 60],
        ['Welcome Celebration', 30, 80],
    ];

    private array $customers = [
        'Maria Dela Cruz', 'Jose Santos', 'Ana Reyes', 'Pedro Bautista', 'Rosa Mendoza',
        'Juan Villareal', 'Lorna Aguilar', 'Ramon Castro', 'Elena Fernandez', 'Antonio Ramos',
        'Teresita Lim', 'Roberto Tan', 'Gloria Manalo', 'Ernesto Valdez', 'Cecilia Ocampo',
        'Fernando Diaz', 'Lourdes Soriano', 'Ricardo Padilla', 'Imelda Cabrera', 'Danilo Medina',
        'Marites Galang', 'Rogelio Abad', 'Norma Esguerra', 'Alfredo Sison', 'Victoria Peralta',
    ];

    private array $venues = [
        'San Miguel, Nabua, Camarines Sur', 'San Isidro, Nabua, Camarines Sur', 'San Esteban, Nabua, Camarines Sur',
        'Santa Lucia, Baras, Nabua, Camarines Sur', 'San Juan, Baao, Camarines Sur', 'Caranday, Baao, Camarines Sur',
        'San Juan, Iriga City, Camarines Sur', 'Santo Domingo, Iriga City, Camarines Sur', 'San Roque, Iriga City, Camarines Sur',
        'Masoli, Bato, Camarines Sur', 'Sagrada, Bato, Camarines Sur', 'Santa Elena, Buhi, Camarines Sur',
        'San Ramon, Bula, Camarines Sur', 'Poblacion, Balatan, Camarines Sur', 'Centro, Pili, Camarines Sur',
    ];

    private array $declineReasons = [
        'I have a family emergency that day.',
        'I am sick and cannot work that day.',
        'I already have a prior commitment on that date.',
        'No available transportation going to the venue.',
    ];

    /** item_id => available quantity, kept in sync while seeding */
    private array $stock = [];

    /** item name => EquipmentItem */
    private array $items = [];

    public function run(): void
    {
        mt_srand(20261024); // same data every run

        $admin = User::where('email', 'eserveadmin@gmail.com')->first();
        if (!$admin) {
            $this->command->error('Admin account not found. Run the main seeder first: php artisan db:seed');
            return;
        }

        $this->items = EquipmentItem::all()->keyBy('name')->all();
        if (empty($this->items)) {
            $this->command->error('No equipment items found. Run the main seeder first: php artisan db:seed');
            return;
        }
        foreach ($this->items as $item) {
            $this->stock[$item->id] = (int) $item->available_quantity;
        }

        $emails = collect($this->waiterNames)->map(fn ($name) => $this->emailFor($name));
        if (User::whereIn('email', $emails)->exists()) {
            $this->command->error('Demo data already seeded (demo waiter emails exist). Run migrate:fresh --seed first to reseed.');
            return;
        }

        DB::transaction(function () use ($admin) {
            $waiters = $this->createWaiters();
            $logs = $this->createEvents($admin, $waiters);

            foreach (array_chunk($logs, 500) as $chunk) {
                EquipmentLog::insert($chunk);
            }

            foreach ($this->items as $item) {
                $item->update(['available_quantity' => $this->stock[$item->id]]);
            }

            $this->command->info('Created 20 waiters (password: ' . self::WAITER_PASSWORD . ').');
            $this->command->info('Created ' . self::EVENT_COUNT . ' events, '
                . EventEquipment::count() . ' total allocations, ' . count($logs) . ' new equipment logs.');
        });
    }

    private function emailFor(string $name): string
    {
        // "Joanna Delos Santos" -> joanna@gmail.com
        return strtolower(explode(' ', $name)[0]) . '@gmail.com';
    }

    private function createWaiters(): array
    {
        $waiters = [];

        foreach ($this->waiterNames as $i => $name) {
            $user = User::create([
                'name' => $name,
                'email' => $this->emailFor($name),
                'password' => Hash::make(self::WAITER_PASSWORD),
                'role' => 'waiter',
            ]);

            $waiters[] = Waiter::create([
                'user_id' => $user->id,
                'phone' => '09' . str_pad((string) mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'emergency_contact' => '09' . str_pad((string) mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'address' => $this->venues[$i % count($this->venues)],
                'date_hired' => now()->subMonths(mt_rand(12, 36))->subDays(mt_rand(0, 28))->toDateString(),
                'status' => 'active',
            ]);
        }

        return $waiters;
    }

    /**
     * Creates the events oldest first so stock moves in the right order.
     * Returns the equipment log rows to insert.
     */
    private function createEvents(User $admin, array $waiters): array
    {
        $dates = [];
        for ($i = 0; $i < self::PAST_EVENTS; $i++) {
            $dates[] = now()->subDays(mt_rand(1, 300));
        }
        for ($i = 0; $i < self::TODAY_EVENTS; $i++) {
            $dates[] = now()->copy();
        }
        for ($i = self::PAST_EVENTS + self::TODAY_EVENTS; $i < self::EVENT_COUNT; $i++) {
            $dates[] = now()->addDays(mt_rand(1, 14));
        }
        usort($dates, fn ($a, $b) => $a <=> $b);

        $busy = []; // 'Y-m-d' => [waiter_id, ...]  (a waiter works one event per day)
        $logs = [];

        foreach ($dates as $day) {
            [$type, $minGuests, $maxGuests] = $this->eventTypes[array_rand($this->eventTypes)];
            $guests = (int) (round(mt_rand($minGuests, $maxGuests) / 10) * 10);

            $eventDate = $day->copy()->setTime([11, 12, 15, 16, 17, 18, 19][mt_rand(0, 6)], 0);
            $setupTime = $eventDate->copy()->subHours(mt_rand(3, 5));

            if ($day->isToday()) {
                $status = 'ongoing';
            } elseif ($day->isPast()) {
                $status = 'completed';
            } else {
                $status = 'scheduled';
            }

            $event = new Event([
                'event_name' => $type,
                'customer_name' => $this->customers[array_rand($this->customers)],
                'venue_address' => $this->venues[array_rand($this->venues)],
                'event_date' => $eventDate,
                'setup_time' => $setupTime,
                'guest_count' => $guests,
                'status' => $status,
                'notes' => mt_rand(1, 4) === 1 ? 'Customer requested early setup.' : null,
                'created_by' => $admin->id,
            ]);
            $event->created_at = $eventDate->copy()->subDays(mt_rand(10, 30));
            $event->updated_at = $event->created_at;
            $event->save();

            $handlers = $this->assignWaiters($event, $waiters, $busy, $guests);
            $logs = array_merge($logs, $this->allocateEquipment($event, $admin, $handlers, $guests));
        }

        return $logs;
    }

    /**
     * Attaches waiters to the event. Returns the ids of waiters who can hold equipment
     * (everyone who did not decline).
     */
    private function assignWaiters(Event $event, array $waiters, array &$busy, int $guests): array
    {
        $dayKey = $event->event_date->toDateString();
        $free = array_values(array_filter($waiters, fn ($w) => !in_array($w->id, $busy[$dayKey] ?? [], true)));
        shuffle($free);

        $count = min(count($free), max(3, (int) ceil($guests / 25)));
        $team = array_slice($free, 0, $count);

        $isPast = $event->status !== 'scheduled';
        $rows = [];
        $handlers = [];

        foreach ($team as $i => $waiter) {
            $role = $i === 0 ? 'head_waiter' : ($i === 1 && $count > 3 ? 'setup_crew' : 'waiter');

            // Past/ongoing: mostly confirmed. Upcoming: mix of confirmed and still pending.
            $roll = mt_rand(1, 100);
            if ($i === 0) {
                $confirmation = 'confirmed';
            } elseif ($roll <= 8) {
                $confirmation = 'declined';
            } elseif (!$isPast && $roll <= 45) {
                $confirmation = 'pending';
            } else {
                $confirmation = 'confirmed';
            }

            $respondedAt = $confirmation === 'pending' ? null : $event->created_at->copy()->addDays(mt_rand(1, 5));

            $timeIn = null;
            $timeOut = null;
            if ($confirmation === 'confirmed' && $event->status === 'completed') {
                $timeIn = $event->setup_time->copy()->addMinutes(mt_rand(-15, 20));
                $timeOut = $event->event_date->copy()->addHours(mt_rand(4, 6))->addMinutes(mt_rand(0, 45));
            } elseif ($confirmation === 'confirmed' && $event->status === 'ongoing') {
                $timeIn = $event->setup_time->copy()->addMinutes(mt_rand(-15, 20));
            }

            $rows[] = [
                'event_id' => $event->id,
                'waiter_id' => $waiter->id,
                'confirmation_status' => $confirmation,
                'decline_reason' => $confirmation === 'declined' ? $this->declineReasons[array_rand($this->declineReasons)] : null,
                'role' => $role,
                'confirmed_at' => $respondedAt,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'created_at' => $event->created_at,
                'updated_at' => $respondedAt ?? $event->created_at,
            ];

            if ($confirmation !== 'declined') {
                $busy[$dayKey][] = $waiter->id;
                $handlers[] = $waiter->id;
            }
        }

        DB::table('event_waiter')->insert($rows);

        return $handlers;
    }

    /**
     * Equipment needed for a given guest count (item name => quantity).
     */
    private function requirements(int $guests): array
    {
        $chafing = (int) ceil($guests / 20) + 2;

        $needs = [
            'Round Dinner Plate' => $guests,
            'Silver Spoon and Fork' => $guests,
            'Highball Glass' => $guests,
            'Chafing Dish' => $chafing,
            'Serving Spoon' => $chafing,
            'Serving Tong' => 1,
            'Juice Dispenser' => $guests > 80 ? 2 : 1,
            'Water Jug' => 2,
            'Pitcher' => 2,
            'Cooler / Styro' => $guests > 80 ? 3 : 2,
            'Extension' => 1,
        ];

        if ($guests >= 60 && mt_rand(1, 2) === 1) {
            $needs['Dessert Plate'] = min($guests, 60);
        }
        if (mt_rand(1, 3) === 1) {
            $tables = (int) ceil($guests / 10);
            $needs['Banquet Table'] = $tables;
            $needs['White Tablecloth'] = $tables;
            $needs['Banquet Chair'] = $guests;
            $needs['Chair Cloth Cover'] = $guests;
        }
        if (mt_rand(1, 3) === 1) {
            $needs['Food Warmer'] = mt_rand(1, 3);
            $needs['Burner'] = mt_rand(2, 4);
        }

        return $needs;
    }

    /**
     * Creates the allocations (handed to the event's waiters) and returns their log rows.
     */
    private function allocateEquipment(Event $event, User $admin, array $handlers, int $guests): array
    {
        $logs = [];
        $allocatedAt = $event->setup_time->copy()->subDays(mt_rand(1, 2))->setTime(mt_rand(8, 16), mt_rand(0, 59));
        if ($allocatedAt->isFuture()) {
            $allocatedAt = now()->subHours(mt_rand(1, 6)); // upcoming events: equipment already set aside
        }
        $returnedAt = $event->event_date->copy()->addHours(mt_rand(5, 8));
        if ($returnedAt->isFuture()) {
            $returnedAt = now()->subMinutes(mt_rand(10, 120));
        }
        $n = 0;

        foreach ($this->requirements($guests) as $name => $wanted) {
            $item = $this->items[$name] ?? null;
            if (!$item) {
                continue;
            }

            $qty = min($wanted, $this->stock[$item->id]);
            if ($qty <= 0) {
                continue; // out of stock: skip, same as the app would refuse it
            }

            $this->stock[$item->id] -= $qty;
            $waiterId = $handlers ? $handlers[$n++ % count($handlers)] : null;

            $allocation = new EventEquipment([
                'event_id' => $event->id,
                'equipment_item_id' => $item->id,
                'assigned_waiter_id' => $waiterId,
                'quantity_allocated' => $qty,
                'status' => $event->status === 'ongoing' ? 'in_use' : 'allocated',
            ]);
            $allocation->created_at = $allocatedAt;
            $allocation->updated_at = $allocatedAt;
            $allocation->save();

            $logs[] = $this->logRow($allocation->id, $admin->id, 'allocated', $qty, 'Equipment allocated for the event.', $allocatedAt);

            if ($event->status !== 'completed') {
                continue; // upcoming / ongoing: equipment is still out
            }

            // Losses only on bulk items, and rarely
            $damaged = 0;
            $missing = 0;
            if ($item->total_quantity >= 100 && mt_rand(1, 100) <= 12) {
                $damaged = mt_rand(0, 3);
                $missing = mt_rand(0, 2);
            }
            $returned = $qty - $damaged - $missing;

            $notes = match (true) {
                $damaged > 0 && $missing > 0 => "{$damaged} damaged and {$missing} missing after the event.",
                $damaged > 0 => "{$damaged} damaged (chipped / broken) on return.",
                $missing > 0 => "{$missing} not returned.",
                default => 'Equipment returned in good condition.',
            };

            $allocation->fill([
                'quantity_returned' => $returned,
                'quantity_damaged' => $damaged,
                'quantity_missing' => $missing,
                'status' => 'returned',
                'notes' => $notes,
                'returned_at' => $returnedAt,
            ]);
            $allocation->updated_at = $returnedAt;
            $allocation->save();

            $this->stock[$item->id] += $returned;

            if ($returned > 0) {
                $logs[] = $this->logRow($allocation->id, $admin->id, 'returned', $returned, $notes, $returnedAt);
            }
            if ($damaged > 0) {
                $logs[] = $this->logRow($allocation->id, $admin->id, 'damaged', $damaged, $notes, $returnedAt);
            }
            if ($missing > 0) {
                $logs[] = $this->logRow($allocation->id, $admin->id, 'missing', $missing, $notes, $returnedAt);
            }
        }

        return $logs;
    }

    private function logRow(int $allocationId, int $adminId, string $action, int $qty, string $remarks, Carbon $at): array
    {
        return [
            'event_equipment_id' => $allocationId,
            'logged_by' => $adminId,
            'action' => $action,
            'quantity' => $qty,
            'remarks' => $remarks,
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
