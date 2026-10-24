<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Event;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use App\Models\Waiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WaiterController extends Controller
{
    public function index(Request $request)
    {
        $query = Waiter::with('user');

        // Search by name, email, or phone
        // (wrapped in one group so the OR doesn't bypass the status/date filters below)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by hire date range
        if ($request->filled('from')) {
            $query->whereDate('date_hired', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date_hired', '<=', $request->to);
        }

        $waiters = $query->latest()->paginate(10)->withQueryString();

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
            'date_hired' => 'required|date|before_or_equal:today',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'required|string|min:8|confirmed',
            'region_id' => 'required|string|exists:regions,region_id',
            'province_id' => 'required|string|exists:provinces,province_id',
            'city_id' => 'required|string|exists:cities,city_id',
            'barangay_code' => 'required|string|exists:barangays,code',
        ], [
            'date_hired.required' => 'Please enter the date hired.',
            'date_hired.before_or_equal' => 'Date hired cannot be in the future.',
            'photo.image' => 'The photo must be an image.',
            'photo.max' => 'The photo must not be larger than 2MB.',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('waiters', 'public')
            : null;

        try {
            DB::transaction(function () use ($validated, $photoPath) {
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
                    'date_hired' => $validated['date_hired'],
                    'photo' => $photoPath,
                    'status' => 'active',
                    'region_id' => $validated['region_id'],
                    'province_id' => $validated['province_id'],
                    'city_id' => $validated['city_id'],
                    'barangay_code' => $validated['barangay_code'],
                ]);
            });
        } catch (\Throwable $e) {
            // Don't leave an orphan image behind if saving failed
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $e;
        }

        return redirect()->route('waiters.index')->with('success', 'Waiter added successfully.');
    }

    public function show(Waiter $waiter)
    {
        $waiter->load([
            'user', 'region', 'province', 'city', 'barangay', 'events',
            'assignedEquipment.event', 'assignedEquipment.equipmentItem',
        ]);
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
            'date_hired' => 'required|date|before_or_equal:today',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_photo' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'region_id' => 'required|string|exists:regions,region_id',
            'province_id' => 'required|string|exists:provinces,province_id',
            'city_id' => 'required|string|exists:cities,city_id',
            'barangay_code' => 'required|string|exists:barangays,code',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'date_hired.required' => 'Please enter the date hired.',
            'date_hired.before_or_equal' => 'Date hired cannot be in the future.',
            'photo.image' => 'The photo must be an image.',
            'photo.max' => 'The photo must not be larger than 2MB.',
        ]);

        $waiter->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $waiterData = [
            'phone' => $validated['phone'],
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_hired' => $validated['date_hired'],
            'status' => $validated['status'],
            'region_id' => $validated['region_id'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'barangay_code' => $validated['barangay_code'],
        ];

        // Photo: replace with a new upload, or remove the current one
        $oldPhoto = $waiter->photo;
        if ($request->hasFile('photo')) {
            $waiterData['photo'] = $request->file('photo')->store('waiters', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $waiterData['photo'] = null;
        }

        if ($request->boolean('change_password') && !empty($validated['password'])) {
            $waiter->user->update(['password' => Hash::make($validated['password'])]);
        }

        $waiter->update($waiterData);

        // Delete the old file only after the new data saved successfully
        if (array_key_exists('photo', $waiterData) && $oldPhoto && $oldPhoto !== $waiterData['photo']) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('waiters.show', $waiter)->with('success', 'Waiter updated successfully.');
    }

    public function destroy(Waiter $waiter)
    {
        $photo = $waiter->photo;

        $waiter->user->delete(); // cascades or handles via foreign key

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        return redirect()->route('waiters.index')->with('success', 'Waiter deleted.');
    }

    /**
     * JSON feed for the calendar on the Waiters page:
     * ONE entry per event, carrying the full list of waiters assigned to it, so two events
     * on the same day always show up as two separate cards, each with its own waiters.
     * Cancelled events and events with no (matching) waiters are left out.
     * FullCalendar sends ?start=...&end=... for the visible range.
     */
    public function calendarFeed(Request $request)
    {
        $tz = config('app.timezone');
        $start = Carbon::parse($request->query('start'))->setTimezone($tz);
        $end = Carbon::parse($request->query('end'))->setTimezone($tz);

        $search = trim((string) $request->query('search'));
        $status = strtolower(trim((string) $request->query('status')));

        $events = Event::query()
            ->whereBetween('event_date', [$start, $end])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            })
            ->with(['waiters' => function ($q) use ($search, $status) {
                $q->with('user');

                if ($status !== '') {
                    $q->where('waiters.status', $status);
                }

                if ($search !== '') {
                    $q->where(function ($w) use ($search) {
                        $w->where('waiters.phone', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
                }
            }])
            ->orderBy('event_date')
            ->get();

        $entries = [];
        foreach ($events as $event) {
            if ($event->waiters->isEmpty()) {
                continue;
            }

            $waiters = $event->waiters->map(function ($waiter) {
                return [
                    'name' => $waiter->user->name ?? 'Unknown',
                    'role' => $waiter->pivot->role,
                    'confirmation' => strtolower(trim((string) $waiter->pivot->confirmation_status)) ?: 'pending',
                ];
            })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

            $entries[] = [
                'id' => 'event-' . $event->id,
                'title' => $event->event_name,
                'start' => $event->event_date->toIso8601String(),
                'url' => route('events.show', $event),
                'extendedProps' => [
                    'customer' => $event->customer_name,
                    'venue' => $event->venue_address,
                    'waiters' => $waiters,
                ],
            ];
        }

        return response()->json($entries);
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

    /**
     * Logged-in waiter changes their own login email (from My Profile).
     * Requires the current password so someone at an unlocked screen can't hijack the account.
     */
    public function updateMyEmail(Request $request)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('updateEmail', [
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required|current_password',
        ], [
            'email.unique' => 'That email is already used by another account.',
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        if (strcasecmp($validated['email'], $user->email) === 0) {
            return redirect()->route('waiters.my-profile')->with('email_success', 'No changes — that is already your email.');
        }

        $user->update(['email' => $validated['email']]);

        return redirect()->route('waiters.my-profile')->with('email_success', 'Your email has been updated.');
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