<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use Illuminate\Http\Request;

class EquipmentItemController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentItem::with('category');

        // Search by item name or serial code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('serial_code', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by stock level
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'low':
                    $query->whereColumn('available_quantity', '<=', \DB::raw('total_quantity * 0.2'))
                        ->where('available_quantity', '>', 0);
                    break;
                case 'out':
                    $query->where('available_quantity', 0);
                    break;
                case 'available':
                    $query->where('available_quantity', '>', 0);
                    break;
            }
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('equipment-items.index', compact('items'));
    }

    public function create()
    {
        $categories = EquipmentCategory::all();
        return view('equipment-items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255',
            'serial_code' => 'nullable|string|unique:equipment_items',
            'total_quantity' => 'required|integer|min:0',
            'condition' => 'in:new,good,fair,damaged',
        ]);

        EquipmentItem::create([
            ...$validated,
            'available_quantity' => $validated['total_quantity'],
        ]);

        return redirect()->route('equipment-items.index')->with('success', 'Equipment item added.');
    }

    public function show(EquipmentItem $equipmentItem)
    {
        $equipmentItem->load('category', 'eventAllocations.event');
        return view('equipment-items.show', compact('equipmentItem'));
    }

    public function edit(EquipmentItem $equipmentItem)
    {
        $categories = EquipmentCategory::all();
        return view('equipment-items.edit', compact('equipmentItem', 'categories'));
    }

    public function update(Request $request, EquipmentItem $equipmentItem)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255',
            'serial_code' => 'nullable|string|unique:equipment_items,serial_code,' . $equipmentItem->id,
            'total_quantity' => 'required|integer|min:' . ($equipmentItem->total_quantity - $equipmentItem->available_quantity),
            'condition' => 'in:new,good,fair,damaged',
        ]);

        $diff = $validated['total_quantity'] - $equipmentItem->total_quantity;

        $equipmentItem->update([
            ...$validated,
            'available_quantity' => $equipmentItem->available_quantity + $diff,
        ]);

        return redirect()->route('equipment-items.show', $equipmentItem)->with('success', 'Equipment item updated.');
    }

    public function destroy(EquipmentItem $equipmentItem)
    {
        if ($equipmentItem->eventAllocations()->count() > 0) {
            return back()->with('error', 'Cannot delete item with allocation history.');
        }
        $equipmentItem->delete();
        return redirect()->route('equipment-items.index')->with('success', 'Equipment item deleted.');
    }
}