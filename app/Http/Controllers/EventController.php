<?php

namespace App\Http\Controllers;

use App\Mail\EventAssignmentMail;
use App\Models\Event;
use App\Models\Waiter;
use App\Notifications\EventAssignedNotification;
use App\Notifications\EventReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
                    ->orWhere('venue_address', 'like', "%{$search}%");
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

    public function create()
    {
        $waiters = Waiter::where('status', 'active')->with('user')->get();
        return view('events.create', compact('waiters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
            'event_date' => 'required|date|after:now',
            'setup_time' => 'required|date|before:event_date',
            'guest_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'waiters' => 'nullable|array',
            'waiters.*.id' => 'exists:waiters,id',
            'waiters.*.role' => 'in:head_waiter,waiter,setup_crew',
        ]);

        $event = Event::create([
            'event_name' => $validated['event_name'],
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

                $waiter = Waiter::find($waiterData['id']);

                // Send via Notification (database + mail)
                $waiter->user->notify(new EventAssignedNotification($event));

                // ALSO send direct Mailable for better Gmail formatting
                try {
                    Mail::to($waiter->user->email)->send(new EventAssignmentMail($event, $waiter->user));
                } catch (\Exception $e) {
                    \Log::error('Mail failed for ' . $waiter->user->email . ': ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('events.show', $event)->with('success', 'Event created and notifications sent to ' . count($validated['waiters'] ?? []) . ' waiter(s).');
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

        $oldDate = $event->event_date;

        $event->update([
            'event_name' => $validated['event_name'],
            'venue_address' => $validated['venue_address'],
            'event_date' => $validated['event_date'],
            'setup_time' => $validated['setup_time'],
            'guest_count' => $validated['guest_count'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync waiters
        $event->waiters()->detach();
        if (!empty($validated['waiters'])) {
            foreach ($validated['waiters'] as $waiterData) {
                if (!isset($waiterData['id']))
                    continue;

                $event->waiters()->attach($waiterData['id'], [
                    'role' => $waiterData['role'] ?? 'waiter',
                    'confirmation_status' => 'pending',
                ]);

                $waiter = Waiter::find($waiterData['id']);

                // Send notification
                $waiter->user->notify(new EventAssignedNotification($event));

                // Send direct email
                try {
                    Mail::to($waiter->user->email)->send(new EventAssignmentMail($event, $waiter->user));
                } catch (\Exception $e) {
                    \Log::error('Mail failed: ' . $e->getMessage());
                }
            }
        }

        // Notify confirmed waiters if date changed
        if ($oldDate != $validated['event_date']) {
            foreach ($event->confirmedWaiters as $waiter) {
                $waiter->user->notify(new EventReminderNotification($event, 'update'));
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

        // Update the pivot
        $event->waiters()->updateExistingPivot($waiter->id, [
            'confirmation_status' => $status,
            'confirmed_at' => $status === 'confirmed' ? now() : null,
        ]);

        // Notify admin when CONFIRMED
        if ($status === 'confirmed') {
            $admin = $event->creator;
            if ($admin) {
                $admin->notify(new \App\Notifications\WaiterConfirmedNotification($event, $waiter));
            }
        }

        // Notify admin only when DECLINED
        if ($status === 'declined') {
            $admin = $event->creator;
            if ($admin) {
                $admin->notify(new \App\Notifications\EventDeclinedNotification($event, $waiter));
            }
        }

        $message = $status === 'confirmed'
            ? 'Participation confirmed successfully.'
            : 'Participation declined. Admin has been notified.';

        return back()->with('success', $message);
    }
}