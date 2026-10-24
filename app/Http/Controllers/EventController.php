<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Waiter;
use App\Notifications\EventAssignedNotification;
use App\Notifications\EventDeclinedNotification;
use App\Notifications\EventReminderNotification;
use App\Notifications\WaiterConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount('waiters', 'equipment');

        // Search by event name or venue address
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue_address', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('event_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('event_date', '<=', $request->to);
        }

        $events = $query->latest()->paginate(10)->withQueryString();

        return view('events.index', compact('events'));
    }

    /**
     * JSON feed for the calendar view on the Events page.
     * FullCalendar sends ?start=...&end=... for the visible range.
     */
    public function feed(Request $request)
    {
        $tz = config('app.timezone');
        $start = Carbon::parse($request->query('start'))->setTimezone($tz);
        $end = Carbon::parse($request->query('end'))->setTimezone($tz);

        $query = Event::query()->whereBetween('event_date', [$start, $end]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue_address', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->get()->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->event_name,
                'start' => $event->event_date->toIso8601String(),
                'url' => route('events.show', $event),
                'extendedProps' => [
                    'status' => strtolower(trim((string) $event->status)),
                    'venue' => $event->venue_address,
                    'customer' => $event->customer_name,
                ],
            ])->values()
        );
    }

    /**
     * Availability check used by the create/edit forms.
     * For one day: the other events scheduled that day, and which waiters are already assigned to them.
     */
    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'exclude' => 'nullable|integer',
        ]);

        $day = Carbon::parse($request->date)->toDateString();

        $events = Event::whereDate('event_date', $day)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            })
            ->when($request->filled('exclude'), fn ($q) => $q->where('id', '!=', $request->exclude))
            ->with('waiters')
            ->orderBy('event_date')
            ->get();

        // waiter id => list of events they are already on that day (declined waiters are free)
        $busy = [];
        foreach ($events as $event) {
            foreach ($event->waiters as $waiter) {
                $confirmation = strtolower(trim((string) $waiter->pivot->confirmation_status));
                if ($confirmation === 'declined') {
                    continue;
                }

                $busy[$waiter->id][] = [
                    'event_id' => $event->id,
                    'event_name' => $event->event_name,
                    'time' => $event->event_date->format('h:i A'),
                    'role' => $waiter->pivot->role,
                    'confirmation' => $confirmation ?: 'pending',
                ];
            }
        }

        return response()->json([
            'events' => $events->map(fn ($event) => [
                'id' => $event->id,
                'name' => $event->event_name,
                'customer' => $event->customer_name,
                'time' => $event->event_date->format('h:i A'),
                'url' => route('events.show', $event),
                'waiter_count' => $event->waiters->count(),
            ])->values(),
            'busy' => (object) $busy,
        ]);
    }

    /**
     * Waiters (from the given ids) who are already assigned to ANOTHER event on the same day.
     * Cancelled events and waiters who declined do not count.
     * Returns [waiter_id => ['name' => ..., 'event' => ..., 'time' => ...]]
     */
    private function waiterConflicts(array $waiterIds, $date, ?int $excludeEventId = null): array
    {
        if (empty($waiterIds)) {
            return [];
        }

        $day = Carbon::parse($date)->toDateString();

        $events = Event::whereDate('event_date', $day)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            })
            ->when($excludeEventId, fn ($q) => $q->where('id', '!=', $excludeEventId))
            ->with(['waiters' => function ($q) use ($waiterIds) {
                $q->whereIn('waiters.id', $waiterIds)->with('user');
            }])
            ->get();

        $conflicts = [];
        foreach ($events as $other) {
            foreach ($other->waiters as $waiter) {
                if (strtolower(trim((string) $waiter->pivot->confirmation_status)) === 'declined') {
                    continue;
                }
                $conflicts[$waiter->id] = [
                    'name' => $waiter->user->name ?? 'A waiter',
                    'event' => $other->event_name,
                    'time' => $other->event_date->format('h:i A'),
                ];
            }
        }

        return $conflicts;
    }

    /** Stop the save (back to the form with an error) if any of these waiters is already busy that day. */
    private function throwIfWaitersBusy(array $waiterIds, $date, ?int $excludeEventId = null): void
    {
        $conflicts = $this->waiterConflicts($waiterIds, $date, $excludeEventId);
        if (!$conflicts) {
            return;
        }

        $messages = collect($conflicts)
            ->map(fn ($c) => "{$c['name']} is already assigned to \"{$c['event']}\" ({$c['time']}) on that day.")
            ->values()
            ->all();

        throw \Illuminate\Validation\ValidationException::withMessages(['waiters' => $messages]);
    }

    public function create()
    {
        $waiters = Waiter::where('status', 'active')->with('user')->get();
        return view('events.create', compact('waiters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
            'event_date' => 'required|date|after:now',
            'setup_time' => 'required|date|before:event_date',
            'guest_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'waiters' => 'nullable|array',
            'waiters.*.id' => 'exists:waiters,id',
            'waiters.*.role' => 'in:head_waiter,waiter,setup_crew',
        ]);

        // A waiter who is already on another event that day cannot be assigned again
        $this->throwIfWaitersBusy(
            collect($validated['waiters'] ?? [])
                ->filter(fn ($w) => !empty($w['id']))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all(),
            $validated['event_date']
        );

        $event = Event::create([
            'event_name' => $validated['event_name'],
            'customer_name' => $validated['customer_name'],
            'venue_address' => $validated['venue_address'],
            'event_date' => $validated['event_date'],
            'setup_time' => $validated['setup_time'],
            'guest_count' => $validated['guest_count'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'scheduled',
        ]);

        if (!empty($validated['waiters'])) {
            foreach ($validated['waiters'] as $waiterData) {
                if (!isset($waiterData['id']))
                    continue;

                $event->waiters()->attach($waiterData['id'], [
                    'role' => $waiterData['role'] ?? 'waiter',
                    'confirmation_status' => 'pending',
                ]);

                // Bell notification + email (one email only; a failed email never breaks the page)
                Waiter::with('user')->find($waiterData['id'])?->user?->notify(new EventAssignedNotification($event));
            }
        }

        // Count only the waiters that were actually ticked (the form also sends a role for unticked rows)
        $assignedCount = collect($validated['waiters'] ?? [])->filter(fn ($w) => !empty($w['id']))->count();

        $message = $assignedCount > 0
            ? 'Event created and notifications sent to ' . $assignedCount . ' ' . \Illuminate\Support\Str::plural('waiter', $assignedCount) . '.'
            : 'Event created. No waiters assigned yet.';

        return redirect()->route('events.show', $event)->with('success', $message);
    }

    public function show(Event $event)
    {
        $event->load(['waiters.user', 'equipment.equipmentItem.category', 'equipment.assignedWaiter.user', 'creator']);
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $waiters = Waiter::where('status', 'active')->with('user')->get();
        $event->load('waiters');
        return view('events.edit', compact('event', 'waiters'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
            'event_date' => 'required|date',
            'setup_time' => 'required|date',
            'guest_count' => 'nullable|integer|min:1',
            'status' => 'in:scheduled,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
            'waiters' => 'nullable|array',
            'waiters.*.id' => 'exists:waiters,id',
            'waiters.*.role' => 'in:head_waiter,waiter,setup_crew',
        ]);

        // A waiter who is already on another event that day cannot be assigned again.
        // Check the newly added waiters (or everyone, if the event was moved to another day).
        $submittedIds = collect($validated['waiters'] ?? [])
            ->filter(fn ($w) => !empty($w['id']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $existingIds = $event->waiters()->pluck('waiters.id')->map(fn ($id) => (int) $id)->all();
        $dayChanged = !$event->event_date
            || $event->event_date->toDateString() !== Carbon::parse($validated['event_date'])->toDateString();
        $this->throwIfWaitersBusy(
            $dayChanged ? $submittedIds : array_values(array_diff($submittedIds, $existingIds)),
            $validated['event_date'],
            $event->id
        );

        $oldDate = $event->event_date;

        $event->update([
            'event_name' => $validated['event_name'],
            'customer_name' => $validated['customer_name'],
            'venue_address' => $validated['venue_address'],
            'event_date' => $validated['event_date'],
            'setup_time' => $validated['setup_time'],
            'guest_count' => $validated['guest_count'] ?? null,
            'status' => $validated['status'] ?? $event->status,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync waiters WITHOUT resetting the ones already assigned:
        //  - waiters who stay keep their Confirmed/Declined status and time in/out (only the role is updated)
        //  - waiters who were removed are detached
        //  - only NEWLY added waiters start as "pending" and get notified
        $currentIds = $event->waiters()->pluck('waiters.id')->all();
        $submitted = collect($validated['waiters'] ?? [])
            ->filter(fn ($w) => !empty($w['id']))
            ->keyBy(fn ($w) => (int) $w['id']);

        $removedIds = array_diff($currentIds, $submitted->keys()->all());
        if ($removedIds) {
            $event->waiters()->detach($removedIds);
        }

        $newIds = [];
        foreach ($submitted as $id => $waiterData) {
            $role = $waiterData['role'] ?? 'waiter';

            if (in_array($id, $currentIds)) {
                $event->waiters()->updateExistingPivot($id, ['role' => $role]);
            } else {
                $event->waiters()->attach($id, [
                    'role' => $role,
                    'confirmation_status' => 'pending',
                ]);
                $newIds[] = $id;
            }
        }

        // Newly assigned waiters: bell notification + email
        Waiter::with('user')->whereIn('id', $newIds)->get()
            ->each(fn ($waiter) => $waiter->user?->notify(new EventAssignedNotification($event)));

        // Date/time changed: tell the confirmed waiters who were already on the team
        $dateChanged = !$oldDate || !$oldDate->equalTo(Carbon::parse($validated['event_date']));
        if ($dateChanged) {
            $event->load('confirmedWaiters.user');
            foreach ($event->confirmedWaiters as $waiter) {
                if (in_array($waiter->id, $newIds)) {
                    continue; // they just got the assignment notice
                }
                $waiter->user?->notify(new EventReminderNotification($event, 'update'));
            }
        }

        return redirect()->route('events.show', $event)->with('success', 'Event updated and notifications sent.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    public function confirmParticipation(Event $event, Request $request)
    {
        $waiter = auth()->user()->waiter;
        if (!$waiter) {
            return back()->with('error', 'You are not registered as a waiter.');
        }

        $status = $request->input('status');
        if (!in_array($status, ['confirmed', 'declined'])) {
            return back()->with('error', 'Invalid status.');
        }

        // A reason is required when declining
        $reason = null;
        if ($status === 'declined') {
            $request->validate([
                'decline_reason' => 'required|string|min:5|max:500',
            ], [
                'decline_reason.required' => 'Please tell the owner why you are declining.',
                'decline_reason.min' => 'Please give a short reason (at least 5 characters).',
            ]);
            $reason = trim($request->input('decline_reason'));
        }

        // Update the pivot (confirmed_at = when the waiter responded, for both confirm and decline)
        $pivot = [
            'confirmation_status' => $status,
            'confirmed_at' => now(),
            'decline_reason' => $reason,
        ];
        if ($status === 'declined') {
            // A declined waiter has no attendance
            $pivot['time_in'] = null;
            $pivot['time_out'] = null;
        }
        $event->waiters()->updateExistingPivot($waiter->id, $pivot);

        // Notify every owner/admin account (bell + email), plus the event creator
        $admins = User::all()->filter(fn ($user) => $user->isAdmin());
        if ($event->creator) {
            $admins->push($event->creator);
        }
        $admins = $admins->unique('id');

        $notification = $status === 'confirmed'
            ? new WaiterConfirmedNotification($event, $waiter)
            : new EventDeclinedNotification($event, $waiter, $reason);

        foreach ($admins as $admin) {
            $admin->notify($notification);
        }

        $message = $status === 'confirmed'
            ? 'Participation confirmed successfully.'
            : 'Participation declined. The owner has been notified.';

        return back()->with('success', $message);
    }
}