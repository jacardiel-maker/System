<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use App\Models\Waiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WaiterController extends Controller
{
    public function index()
    {
        $waiters = Waiter::with('user')->latest()->paginate(10);
        return view('waiters.index', compact('waiters'));
    }

    public function create()
    {
        $regions = Region::orderBy('name')->get();
        return view('waiters.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_hired' => 'nullable|date',
            'password' => 'required|string|min:6|confirmed',
            'region_id' => 'required|string|exists:regions,region_id',
            'province_id' => 'required|string|exists:provinces,province_id',
            'city_id' => 'required|string|exists:cities,city_id',
            'barangay_code' => 'required|string|exists:barangays,code',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'waiter',
        ]);

        Waiter::create([
            'user_id' => $user->id,
            'phone' => $validated['phone'],
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_hired' => $validated['date_hired'] ?? null,
            'status' => 'active',
            'region_id' => $validated['region_id'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'barangay_code' => $validated['barangay_code'],
        ]);

        return redirect()->route('waiters.index')->with('success', 'Waiter added successfully.');
    }

    public function show(Waiter $waiter)
    {
        $waiter->load(['user', 'region', 'province', 'city', 'barangay', 'events']);
        return view('waiters.show', compact('waiter'));
    }

    public function edit(Waiter $waiter)
    {
        $regions = Region::orderBy('name')->get();
        $provinces = Province::where('region_id', $waiter->region_id)->orderBy('name')->get();
        $cities = City::where('province_id', $waiter->province_id)->orderBy('name')->get();
        $barangays = Barangay::where('city_id', $waiter->city_id)->orderBy('name')->get();

        return view('waiters.edit', compact('waiter', 'regions', 'provinces', 'cities', 'barangays'));
    }

    public function update(Request $request, Waiter $waiter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $waiter->user_id,
            'phone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_hired' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'region_id' => 'required|string|exists:regions,region_id',
            'province_id' => 'required|string|exists:provinces,province_id',
            'city_id' => 'required|string|exists:cities,city_id',
            'barangay_code' => 'required|string|exists:barangays,code',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $waiter->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $waiterData = [
            'phone' => $validated['phone'],
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_hired' => $validated['date_hired'] ?? null,
            'status' => $validated['status'],
            'region_id' => $validated['region_id'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'barangay_code' => $validated['barangay_code'],
        ];

        if (!empty($validated['password'])) {
            $waiter->user->update(['password' => Hash::make($validated['password'])]);
        }

        $waiter->update($waiterData);

        return redirect()->route('waiters.show', $waiter)->with('success', 'Waiter updated successfully.');
    }

    public function destroy(Waiter $waiter)
    {
        $waiter->user->delete(); // cascades or handles via foreign key
        return redirect()->route('waiters.index')->with('success', 'Waiter deleted.');
    }

    public function myProfile()
    {
        $waiter = auth()->user()->waiter()->with([
            'region',
            'province',
            'city',
            'barangay',
            'events',
            'assignedEquipment.event',
            'assignedEquipment.equipmentItem'
        ])->firstOrFail();

        return view('waiters.my-profile', compact('waiter'));
    }

    /* ─── AJAX Cascading Dropdowns ─── */
    public function getProvinces($regionId)
    {
        return response()->json(
            Province::where('region_id', $regionId)
                ->orderBy('name')
                ->get()
                ->map(fn($p) => ['id' => (string) $p->province_id, 'name' => $p->name])
        );
    }

    public function getCities($provinceId)
    {
        return response()->json(
            City::where('province_id', $provinceId)
                ->orderBy('name')
                ->get()
                ->map(fn($c) => ['id' => (string) $c->city_id, 'name' => $c->name])
        );
    }

    public function getBarangays($cityId)
    {
        return response()->json(
            Barangay::where('city_id', $cityId)
                ->orderBy('name')
                ->get()
                ->map(fn($b) => ['id' => (string) $b->code, 'name' => $b->name])
        );
    }
}