<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Notifications\EventAssignedNotification;
use App\Notifications\EventDeclinedNotification;
use App\Notifications\WaiterConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bell notifications that match the seeded events, the same ones the app itself would have sent:
 *   - waiter:         "New Event Assignment" for the events they are assigned to
 *   - admin + owner:  "Waiter Confirmed Participation" / "Event Declined" for every waiter response
 *
 * Only recent activity is seeded (events from the last 30 days and upcoming ones, plus each waiter's
 * two latest assignments) so nobody starts with hundreds of old notifications. Anything older than a
 * week is already marked as read. No emails are sent.
 *
 * Safe to run again (php artisan db:seed --class=NotificationSeeder): existing notifications are kept
 * and only the missing ones are added.
 */
class NotificationSeeder extends Seeder
{
    private const RECENT_DAYS = 30;
    private const LATEST_PER_WAITER = 2;
    private const READ_AFTER_DAYS = 7;

    public function run(): void
    {
        $monitors = User::whereIn('role', ['admin', 'owner'])->get();   // they receive the waiters' responses
        $cutoff = now()->subDays(self::RECENT_DAYS);

        // Every assignment, newest event first
        $assignments = DB::table('event_waiter as ew')
            ->join('events as e', 'e.id', '=', 'ew.event_id')
            ->join('waiters as w', 'w.id', '=', 'ew.waiter_id')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->orderByDesc('e.event_date')
            ->get([
                'ew.event_id', 'ew.waiter_id', 'ew.confirmation_status', 'ew.decline_reason', 'ew.confirmed_at',
                'ew.created_at as assigned_at', 'e.event_name', 'e.event_date', 'e.created_at as event_created_at',
                'u.id as user_id', 'u.name as waiter_name',
            ]);

        // Already stored notifications, so running this twice never duplicates anything
        $existing = [];
        foreach (DB::table('notifications')->get(['type', 'notifiable_id', 'data']) as $row) {
            $data = json_decode($row->data, true) ?: [];
            $existing[$this->key($row->type, $row->notifiable_id, $data['event_id'] ?? null, $data['waiter_id'] ?? null)] = true;
        }

        $rows = [];
        $perWaiter = [];
        $add = function (string $type, int $userId, array $data, Carbon $at) use (&$rows, &$existing) {
            $key = $this->key($type, $userId, $data['event_id'] ?? null, $data['waiter_id'] ?? null);
            if (isset($existing[$key])) {
                return;
            }
            $existing[$key] = true;

            if ($at->isFuture()) {
                $at = now()->subMinutes(mt_rand(5, 180));
            }
            $isOld = $at->lt(now()->subDays(self::READ_AFTER_DAYS));

            $rows[] = [
                'id' => (string) Str::uuid(),
                'type' => $type,
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode($data),
                'read_at' => $isOld ? $at->copy()->addHours(mt_rand(1, 20)) : null,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        };

        foreach ($assignments as $a) {
            $eventDate = Carbon::parse($a->event_date);
            $isRecent = $eventDate->gte($cutoff);
            $perWaiter[$a->waiter_id] = ($perWaiter[$a->waiter_id] ?? 0) + 1;

            if (!$isRecent && $perWaiter[$a->waiter_id] > self::LATEST_PER_WAITER) {
                continue;
            }

            $assignedAt = Carbon::parse($a->assigned_at ?? $a->event_created_at ?? $eventDate->copy()->subDays(7));
            if ($assignedAt->gt($eventDate)) {
                $assignedAt = $eventDate->copy()->subDays(7); // assignments are made before the event
            }

            // Waiter: "you are assigned to ..."  (same data as EventAssignedNotification::toArray)
            $add(EventAssignedNotification::class, (int) $a->user_id, [
                'event_id' => (int) $a->event_id,
                'title' => 'New Event Assignment',
                'message' => 'You are assigned to: ' . $a->event_name,
                'event_date' => $eventDate->toJSON(),
            ], $assignedAt);

            // Admin + owner: the waiter's answer, for recent events only
            $status = strtolower(trim((string) $a->confirmation_status));
            if (!$isRecent || !in_array($status, ['confirmed', 'declined'], true)) {
                continue;
            }
            $respondedAt = $a->confirmed_at ? Carbon::parse($a->confirmed_at) : $assignedAt->copy()->addDay();

            foreach ($monitors as $monitor) {
                if ($status === 'confirmed') {
                    $add(WaiterConfirmedNotification::class, $monitor->id, [
                        'event_id' => (int) $a->event_id,
                        'waiter_id' => (int) $a->waiter_id,
                        'waiter_name' => $a->waiter_name,
                        'title' => 'Waiter Confirmed Participation',
                        'message' => $a->waiter_name . ' confirmed for: ' . $a->event_name,
                        'event_name' => $a->event_name,
                        'event_date' => $eventDate->toJSON(),
                        'type' => 'waiter_confirmed',
                    ], $respondedAt->copy());
                } else {
                    $add(EventDeclinedNotification::class, $monitor->id, [
                        'event_id' => (int) $a->event_id,
                        'event_name' => $a->event_name,
                        'waiter_id' => (int) $a->waiter_id,
                        'waiter_name' => $a->waiter_name,
                        'title' => 'Event Declined',
                        'message' => $a->waiter_name . ' declined the event: ' . $a->event_name,
                        'declined_at' => $respondedAt->toDateTimeString(),
                        'reason' => $a->decline_reason ?: 'No reason given.',
                        'type' => 'event_declined',
                    ], $respondedAt->copy());
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }

        $this->command->info('Notifications matched to the events: ' . count($rows) . ' added for the waiters, admin and owner.');
    }

    private function key(string $type, $userId, $eventId, $waiterId): string
    {
        return $type . '|' . $userId . '|' . $eventId . '|' . $waiterId;
    }
}
