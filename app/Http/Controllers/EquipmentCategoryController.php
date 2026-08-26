<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use Illuminate\Http\Request;

class EquipmentCategoryController extends Controller
{
    public function index()
    {
        $categories = EquipmentCategory::withCount('items')->latest()->paginate(10);
        return view('equipment-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('equipment-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:equipment_categories',
            'description' => 'nullable|string',
        ]);

        EquipmentCategory::create($validated);

        return redirect()->route('equipment-categories.index')->with('success', 'Category created.');
    }

    public function show(EquipmentCategory $equipmentCategory)
    {
        $equipmentCategory->load('items');
        return view('equipment-categories.show', compact('equipmentCategory'));
    }

    public function edit(EquipmentCategory $equipmentCategory)
    {
        return view('equipment-categories.edit', compact('equipmentCategory'));
    }

    public function update(Request $request, EquipmentCategory $equipmentCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:equipment_categories,code,' . $equipmentCategory->id,
            'description' => 'nullable|string',
        ]);

        $equipmentCategory->update($validated);

        return redirect()->route('equipment-categories.show', $equipmentCategory)->with('success', 'Category updated.');
    }

    public function destroy(EquipmentCategory $equipmentCategory)
    {
        if ($equipmentCategory->items()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing items.');
        }
        
        $equipmentCategory->delete();
        return redirect()->route('equipment-categories.index')->with('success', 'Category deleted.');
    }
}