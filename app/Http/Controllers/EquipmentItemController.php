<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use Illuminate\Http\Request;

class EquipmentItemController extends Controller
{
    public function index()
    {
        $items = EquipmentItem::with('category')->latest()->paginate(10);
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