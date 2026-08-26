@extends('layouts.app')
@section('title', $waiter->user->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('waiters.index') }}" class="hover:text-amber-700 transition-colors">Waiters</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $waiter->user->name }}</span>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Waiter Profile</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('waiters.edit', $waiter) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Waiter
            </a>
            <form action="{{ route('waiters.destroy', $waiter) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this waiter? This action cannot be undone.');">
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Profile Sidebar --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-6">
                {{-- Profile Header --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center border-b border-slate-100">
                    <div class="relative inline-block">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                            <span class="text-3xl font-bold text-amber-600">
                                {{ strtoupper(substr($waiter->user->name, 0, 1)) }}
                            </span>
                        </div>
                        <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full border-2 border-white {{ $waiter->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $waiter->user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $waiter->user->email }}</p>
                    <span class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-semibold {{ $waiter->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $waiter->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }} mr-1.5"></span>
                        {{ ucfirst($waiter->status) }}
                    </span>
                </div>

                {{-- Contact Details --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Phone</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->phone }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Emergency Contact</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->emergency_contact ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Date Hired</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $waiter->date_hired?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Address</p>
                                <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $waiter->full_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-6">
            {{-- Event History --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Event History</h2>
                            <p class="text-xs text-slate-500">{{ $waiter->events->count() }} events</p>
                        </div>
                    </div>
                </div>

                @if($waiter->events->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($waiter->events as $event)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                            {{ $event->event_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $event->event_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ ucfirst($event->pivot->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusConfig = [
                                                    'confirmed' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Confirmed'],
                                                    'pending' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Pending'],
                                                    'declined' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Declined'],
                                                ];
                                                $config = $statusConfig[$event->pivot->confirmation_status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($event->pivot->confirmation_status)];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $config[0] }}">
                                                {{ $config[1] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No events yet</h3>
                        <p class="mt-1 text-sm text-slate-500">This waiter hasn't been assigned to any events.</p>
                    </div>
                @endif
            </div>

            {{-- Equipment Accountability --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Equipment Accountability</h2>
                            <p class="text-xs text-slate-500">{{ $waiter->assignedEquipment->count() }} items</p>
                        </div>
                    </div>
                </div>

                @if($waiter->assignedEquipment->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($waiter->assignedEquipment as $eq)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                            {{ $eq->event->event_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $eq->equipmentItem->name }}
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No equipment assigned</h3>
                        <p class="mt-1 text-sm text-slate-500">This waiter doesn't have any equipment checked out.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection