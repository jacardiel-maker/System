<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class EquipmentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentCategory::withCount('items');

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'items':
                $query->orderBy('items_count', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('equipment-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('equipment-categories.create');
    }

    public function store(Request $request)
    {
        // Codes are stored in uppercase (e.g. "plt" -> "PLT") so "PLT" and "plt" can't both exist
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:equipment_categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'code.unique' => 'That code is already used by another category.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPG, PNG or WEBP file.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        $data = Arr::except($validated, ['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        try {
            EquipmentCategory::create($data);
        } catch (\Throwable $e) {
            // Don't leave an orphan image behind if saving failed
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $e;
        }

        return redirect()->route('equipment-categories.index')->with('success', 'Category created.');
    }

    public function show(EquipmentCategory $equipmentCategory)
    {
        $equipmentCategory->load(['items' => fn ($q) => $q->orderBy('name')]);
        return view('equipment-categories.show', compact('equipmentCategory'));
    }

    public function edit(EquipmentCategory $equipmentCategory)
    {
        return view('equipment-categories.edit', compact('equipmentCategory'));
    }

    public function update(Request $request, EquipmentCategory $equipmentCategory)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:equipment_categories,code,' . $equipmentCategory->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ], [
            'code.unique' => 'That code is already used by another category.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPG, PNG or WEBP file.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        $data = Arr::except($validated, ['image', 'remove_image']);

        // Image: replace with a new upload, remove, or keep as is
        $oldImage = $equipmentCategory->image;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $equipmentCategory->update($data);

        // Delete the old file only after the new data saved successfully
        if ($oldImage && array_key_exists('image', $data) && $oldImage !== $data['image']) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('equipment-categories.show', $equipmentCategory)->with('success', 'Category updated.');
    }

    public function destroy(EquipmentCategory $equipmentCategory)
    {
        if ($equipmentCategory->items()->count() > 0) {
            return back()->with('error', 'Cannot delete a category that still has items. Move or delete its items first.');
        }

        $image = $equipmentCategory->image;

        $equipmentCategory->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('equipment-categories.index')->with('success', 'Category deleted.');
    }
}