<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventEquipment;
use App\Models\EquipmentItem;
use App\Models\Waiter;
use App\Models\EquipmentLog;
use Illuminate\Http\Request;

class EventEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = EventEquipment::with(['event', 'equipmentItem', 'assignedWaiter.user']);

        // Search: event name, equipment name, or waiter name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('event', function ($eq) use ($search) {
                    $eq->where('event_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('equipmentItem', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedWaiter.user', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned waiter
        if ($request->filled('waiter_id')) {
            $query->where('assigned_waiter_id', $request->waiter_id);
        }

        // Filter by event date range
        if ($request->filled('from')) {
            $query->whereHas('event', function ($q) use ($request) {
                $q->whereDate('event_date', '>=', $request->from);
            });
        }
        if ($request->filled('to')) {
            $query->whereHas('event', function ($q) use ($request) {
                $q->whereDate('event_date', '<=', $request->to);
            });
        }

        $allocations = $query->latest()->paginate(15)->withQueryString();

        return view('event-equipment.index', compact('allocations'));
    }

    public function create()
    {
        $events = Event::whereIn('status', ['scheduled', 'ongoing'])->get();
        $items = EquipmentItem::where('available_quantity', '>', 0)->with('category')->get();
        $waiters = Waiter::where('status', 'active')->with('user')->get();
        return view('event-equipment.create', compact('events', 'items', 'waiters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'assigned_waiter_id' => 'required|exists:waiters,id',
            'equipment' => 'required|array|min:1',
            'equipment.*.selected' => 'nullable|in:1',
            'equipment.*.quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // Filter only selected items
        $selectedEquipment = collect($validated['equipment'])->filter(function ($item) {
            return isset($item['selected']) && $item['selected'] == 1 && isset($item['quantity']) && $item['quantity'] > 0;
        });

        if ($selectedEquipment->isEmpty()) {
            return back()->with('error', 'Please select at least one equipment item.')->withInput();
        }

        $eventId = $validated['event_id'];
        $waiterId = $validated['assigned_waiter_id'];
        $notes = $validated['notes'] ?? null;
        $successCount = 0;
        $errors = [];

        foreach ($selectedEquipment as $itemId => $itemData) {
            $quantity = (int) $itemData['quantity'];

            $equipmentItem = EquipmentItem::find($itemId);
            if (!$equipmentItem) {
                $errors[] = "Item not found (ID: {$itemId})";
                continue;
            }

            if ($equipmentItem->available_quantity < $quantity) {
                $errors[] = "{$equipmentItem->name}: Only {$equipmentItem->available_quantity} available, requested {$quantity}";
                continue;
            }

            try {
                $allocation = EventEquipment::create([
                    'event_id' => $eventId,
                    'equipment_item_id' => $itemId,
                    'assigned_waiter_id' => $waiterId,
                    'quantity_allocated' => $quantity,
                    'status' => 'allocated',
                    'notes' => $notes,
                ]);

                $equipmentItem->decrement('available_quantity', $quantity);

                EquipmentLog::create([
                    'event_equipment_id' => $allocation->id,
                    'logged_by' => auth()->id(),
                    'action' => 'allocated',
                    'quantity' => $quantity,
                    'remarks' => 'Allocated to ' . Waiter::find($waiterId)->user->name,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "{$equipmentItem->name}: " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            $message = "{$successCount} item(s) allocated successfully.";
            if (!empty($errors)) {
                $message .= ' Some items failed: ' . implode(', ', $errors);
            }
            return redirect()->route('event-equipment.index')->with('success', $message);
        }

        return back()->with('error', 'No items could be allocated: ' . implode(', ', $errors))->withInput();
    }

    public function show(EventEquipment $eventEquipment)
    {
        $eventEquipment->load(['event', 'equipmentItem.category', 'assignedWaiter.user', 'logs.logger']);
        return view('event-equipment.show', compact('eventEquipment'));
    }

    public function edit(EventEquipment $eventEquipment)
    {
        if (!in_array($eventEquipment->status, ['allocated', 'in_use'])) {
            return back()->with('error', 'Cannot edit allocation that is already returned or lost.');
        }

        $events = Event::whereIn('status', ['scheduled', 'ongoing'])->get();
        $items = EquipmentItem::with('category')->get();
        $waiters = Waiter::where('status', 'active')->with('user')->get();
        return view('event-equipment.edit', compact('eventEquipment', 'events', 'items', 'waiters'));
    }

    public function update(Request $request, EventEquipment $eventEquipment)
    {
        if (!in_array($eventEquipment->status, ['allocated', 'in_use'])) {
            return back()->with('error', 'Cannot update allocation that is already processed.');
        }

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'equipment_item_id' => 'required|exists:equipment_items,id',
            'assigned_waiter_id' => 'required|exists:waiters,id',
            'quantity_allocated' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $oldItem = $eventEquipment->equipmentItem;
            $oldItem->increment('available_quantity', $eventEquipment->quantity_allocated);

            $newItem = EquipmentItem::findOrFail($validated['equipment_item_id']);

            if ($newItem->available_quantity < $validated['quantity_allocated']) {
                $oldItem->decrement('available_quantity', $eventEquipment->quantity_allocated);
                return back()
                    ->with('error', 'Not enough stock! Only ' . $newItem->available_quantity . ' available.')
                    ->withInput();
            }

            $eventEquipment->update([
                'event_id' => $validated['event_id'],
                'equipment_item_id' => $validated['equipment_item_id'],
                'assigned_waiter_id' => $validated['assigned_waiter_id'],
                'quantity_allocated' => $validated['quantity_allocated'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $newItem->decrement('available_quantity', $validated['quantity_allocated']);

            EquipmentLog::create([
                'event_equipment_id' => $eventEquipment->id,
                'logged_by' => auth()->id(),
                'action' => 'allocated',
                'quantity' => $validated['quantity_allocated'],
                'remarks' => 'Allocation updated',
            ]);

            return redirect()
                ->route('event-equipment.show', $eventEquipment)
                ->with('success', 'Allocation updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(EventEquipment $eventEquipment)
    {
        if (!in_array($eventEquipment->status, ['allocated', 'in_use'])) {
            return back()->with('error', 'Cannot delete allocation that has been processed.');
        }

        try {
            // Delete related logs FIRST (fixes the foreign key error)
            $eventEquipment->logs()->delete();

            // Return quantity to available stock
            $eventEquipment->equipmentItem->increment('available_quantity', $eventEquipment->quantity_allocated);

            // Now delete the allocation
            $eventEquipment->delete();

            return redirect()
                ->route('event-equipment.index')
                ->with('success', 'Allocation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function returnEquipment(Request $request, EventEquipment $eventEquipment)
    {
        $validated = $request->validate([
            'quantity_returned' => 'required|integer|min:0',
            'quantity_damaged' => 'required|integer|min:0',
            'quantity_missing' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $total = $validated['quantity_returned'] + $validated['quantity_damaged'] + $validated['quantity_missing'];

        if ($total > $eventEquipment->quantity_allocated) {
            return back()
                ->with('error', 'Total (' . $total . ') cannot exceed allocated quantity (' . $eventEquipment->quantity_allocated . ').')
                ->withInput();
        }

        try {
            $eventEquipment->update([
                'quantity_returned' => $validated['quantity_returned'],
                'quantity_damaged' => $validated['quantity_damaged'],
                'quantity_missing' => $validated['quantity_missing'],
                'notes' => $validated['notes'],
                'status' => $total == $eventEquipment->quantity_allocated ? 'returned' : 'partially_returned',
                'returned_at' => now(),
            ]);

            $item = $eventEquipment->equipmentItem;
            $item->increment('available_quantity', $validated['quantity_returned']);

            // Log actions
            if ($validated['quantity_returned'] > 0) {
                EquipmentLog::create([
                    'event_equipment_id' => $eventEquipment->id,
                    'logged_by' => auth()->id(),
                    'action' => 'returned',
                    'quantity' => $validated['quantity_returned'],
                    'remarks' => $validated['notes'] ?? 'Good condition',
                ]);
            }

            if ($validated['quantity_damaged'] > 0) {
                EquipmentLog::create([
                    'event_equipment_id' => $eventEquipment->id,
                    'logged_by' => auth()->id(),
                    'action' => 'damaged',
                    'quantity' => $validated['quantity_damaged'],
                    'remarks' => $validated['notes'] ?? 'Damaged on return',
                ]);
            }

            if ($validated['quantity_missing'] > 0) {
                EquipmentLog::create([
                    'event_equipment_id' => $eventEquipment->id,
                    'logged_by' => auth()->id(),
                    'action' => 'missing',
                    'quantity' => $validated['quantity_missing'],
                    'remarks' => $validated['notes'] ?? 'Not returned',
                ]);
            }

            return redirect()
                ->route('event-equipment.show', $eventEquipment)
                ->with('success', 'Equipment return recorded successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }
}
