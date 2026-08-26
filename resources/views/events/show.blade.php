@extends('layouts.app')
@section('title', $event->event_name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('events.index') }}" class="hover:text-amber-700 transition-colors">Events</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $event->event_name }}</span>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $event->event_name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.edit', $event) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Event
            </a>
            <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 hover:border-rose-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Event Details Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            @php
                                $statusConfig = [
                                    'scheduled' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Scheduled'],
                                    'ongoing' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Ongoing'],
                                    'completed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Completed'],
                                    'cancelled' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Cancelled'],
                                ];
                                $config = $statusConfig[$event->status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($event->status)];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $config[0] }}">
                                {{ $config[1] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Event Date</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $event->event_date->format('F d, Y') }}</p>
                                <p class="text-sm text-slate-600">{{ $event->event_date->format('h:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Setup Time</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $event->setup_time->format('F d, Y') }}</p>
                                <p class="text-sm text-slate-600">{{ $event->setup_time->format('h:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 sm:col-span-2">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Venue</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $event->venue_address }}</p>
                            </div>
                        </div>

                        @if($event->guest_count)
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Expected Guests</p>
                                <p class="text-sm font-semibold text-slate-900">{{ number_format($event->guest_count) }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($event->notes)
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $event->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Equipment Allocations --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Equipment Allocations</h2>
                            <p class="text-xs text-slate-500">{{ $event->equipment->count() }} items allocated</p>
                        </div>
                    </div>
                    <a href="{{ route('event-equipment.create') }}?event_id={{ $event->id }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Allocate
                    </a>
                </div>

                @if($event->equipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($event->equipment as $eq)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                            {{ $eq->equipmentItem->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $eq->assignedWaiter->user->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 font-semibold text-slate-700">
                                                {{ $eq->quantity_allocated }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($eq->status === 'returned')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Returned
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ ucfirst(str_replace('_', ' ', $eq->status)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('event-equipment.show', $eq) }}"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"
                                                    title="View">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>

                                                @if($eq->status === 'allocated')
                                                    <a href="{{ route('event-equipment.edit', $eq) }}"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all"
                                                        title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </a>

                                                    <form action="{{ route('event-equipment.destroy', $eq) }}" method="POST" class="inline" onsubmit="return confirm('Delete this allocation? Stock will be returned to inventory.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                                            title="Delete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(!in_array($eq->status, ['returned', 'lost']))
                                                    <button onclick="openReturnModal({{ $eq->id }})"
                                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors"
                                                        title="Record Return">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                        Return
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No equipment allocated</h3>
                        <p class="mt-1 text-sm text-slate-500">Start by allocating equipment to this event.</p>
                        <a href="{{ route('event-equipment.create') }}?event_id={{ $event->id }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-medium text-amber-700 hover:text-amber-800 hover:underline">
                            Allocate equipment
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Assigned Waiters --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Assigned Waiters</h2>
                            <p class="text-xs text-slate-500">{{ $event->waiters->count() }} team members</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    @forelse($event->waiters as $waiter)
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <div class="p-4 bg-slate-50/50">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $waiter->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $waiter->phone }}</p>
                                    </div>
                                    @php
                                        $confConfig = [
                                            'confirmed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Confirmed'],
                                            'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Pending'],
                                            'declined' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Declined'],
                                        ];
                                        $cConfig = $confConfig[$waiter->pivot->confirmation_status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($waiter->pivot->confirmation_status)];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $cConfig[0] }}">
                                        {{ $cConfig[1] }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ ucfirst($waiter->pivot->role) }}
                                </span>
                            </div>

                            {{-- Attendance Form --}}
                            <form action="{{ route('events.waiter.time', [$event, $waiter]) }}" method="POST" class="p-4 border-t border-slate-100">
                                @csrf
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-slate-500">Time In</label>
                                        <input type="datetime-local" name="time_in"
                                            value="{{ $waiter->pivot->time_in ? \Carbon\Carbon::parse($waiter->pivot->time_in)->format('Y-m-d\TH:i') : '' }}"
                                            class="w-full rounded-lg border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-slate-500">Time Out</label>
                                        <input type="datetime-local" name="time_out"
                                            value="{{ $waiter->pivot->time_out ? \Carbon\Carbon::parse($waiter->pivot->time_out)->format('Y-m-d\TH:i') : '' }}"
                                            class="w-full rounded-lg border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="mt-3 w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Attendance
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="text-sm text-slate-500">No waiters assigned yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Return Modal --}}
<div id="returnModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('returnModal').classList.add('hidden')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Record Equipment Return</h3>
            </div>

            <form id="returnForm" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Returned (Good)</label>
                        <input type="number" name="quantity_returned" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all text-center">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Damaged</label>
                        <input type="number" name="quantity_damaged" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all text-center">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Missing</label>
                        <input type="number" name="quantity_missing" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20 transition-all text-center">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none"
                        placeholder="Condition details, issues found..."></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')"
                        class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReturnModal(id) {
    document.getElementById('returnForm').action = '/event-equipment/' + id + '/return';
    document.getElementById('returnModal').classList.remove('hidden');
}
</script>
@endsection