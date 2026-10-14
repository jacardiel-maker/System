<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Models\EquipmentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class EquipmentItemController extends Controller
{
    /**
     * An item is "Low Stock" when its available quantity is below this percentage of its total
     * (e.g. 15 of 100 = 15% → Low Stock). Matches test case TC-INV-A06 in Chapter 3.
     */
    private const LOW_STOCK_PERCENT = 20;

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
                    // available / total < 20%  →  available * 100 < total * 20
                    $query->where('available_quantity', '>', 0)
                        ->whereRaw('available_quantity * 100 < total_quantity * ?', [self::LOW_STOCK_PERCENT]);
                    break;
                case 'out':
                    $query->where('available_quantity', '<=', 0);
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
        $categories = EquipmentCategory::orderBy('name')->get();
        return view('equipment-items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255',
            'serial_code' => 'nullable|string|unique:equipment_items',
            'total_quantity' => 'required|integer|min:0',
            'condition' => 'nullable|in:new,good,fair,damaged',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPG, PNG or WEBP file.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('equipment', 'public')
            : null;

        try {
            EquipmentItem::create([
                ...Arr::except($validated, ['image']),
                'condition' => $validated['condition'] ?? 'good',
                'available_quantity' => $validated['total_quantity'],
                'image' => $imagePath,
            ]);
        } catch (\Throwable $e) {
            // Don't leave an orphan image behind if saving failed
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }

        return redirect()->route('equipment-items.index')->with('success', 'Equipment item added.');
    }

    public function show(EquipmentItem $equipmentItem)
    {
        $equipmentItem->load('category', 'eventAllocations.event', 'eventAllocations.assignedWaiter.user');
        return view('equipment-items.show', compact('equipmentItem'));
    }

    public function edit(EquipmentItem $equipmentItem)
    {
        $categories = EquipmentCategory::orderBy('name')->get();
        return view('equipment-items.edit', compact('equipmentItem', 'categories'));
    }

    public function update(Request $request, EquipmentItem $equipmentItem)
    {
        $allocated = max(0, $equipmentItem->total_quantity - $equipmentItem->available_quantity);

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255',
            'serial_code' => 'nullable|string|unique:equipment_items,serial_code,' . $equipmentItem->id,
            'total_quantity' => 'required|integer|min:' . $allocated,
            'condition' => 'nullable|in:new,good,fair,damaged',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ], [
            'total_quantity.min' => "Total quantity cannot be less than {$allocated} (currently allocated).",
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPG, PNG or WEBP file.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        $diff = $validated['total_quantity'] - $equipmentItem->total_quantity;

        // Image: replace with a new upload, remove, or keep as is
        $oldImage = $equipmentItem->image;
        $newImage = $oldImage;
        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('equipment', 'public');
        } elseif ($request->boolean('remove_image')) {
            $newImage = null;
        }

        $equipmentItem->update([
            ...Arr::except($validated, ['image', 'remove_image']),
            'condition' => $validated['condition'] ?? $equipmentItem->condition,
            'available_quantity' => $equipmentItem->available_quantity + $diff,
            'image' => $newImage,
        ]);

        // Delete the old file only after the new data saved successfully
        if ($oldImage && $oldImage !== $newImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('equipment-items.show', $equipmentItem)->with('success', 'Equipment item updated.');
    }

    public function destroy(EquipmentItem $equipmentItem)
    {
        if ($equipmentItem->eventAllocations()->count() > 0) {
            return back()->with('error', 'Cannot delete item with allocation history.');
        }

        $image = $equipmentItem->image;

        $equipmentItem->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('equipment-items.index')->with('success', 'Equipment item deleted.');
    }
}