@extends('layouts.app')
@section('title', 'New Allocation')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('event-equipment.index') }}" class="hover:text-amber-700 transition-colors">Allocations</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">New Allocation</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Allocate Equipment</h1>
        <p class="mt-1 text-sm text-slate-500">Select multiple items to assign to a waiter for an event</p>
    </div>

    {{-- Error Summary --}}
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-rose-800">Please fix the following errors:</h3>
                    <ul class="mt-1.5 text-sm text-rose-700 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Empty State Alerts --}}
    @if($events->isEmpty())
        <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">No active events found.</p>
                <a href="{{ route('events.create') }}" class="inline-flex items-center gap-1 mt-1 text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline transition-colors">
                    Create an event first
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif

    @if($items->isEmpty())
        <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">No available equipment found.</p>
                <a href="{{ route('equipment-items.create') }}" class="inline-flex items-center gap-1 mt-1 text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline transition-colors">
                    Add equipment first
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif

    @if($waiters->isEmpty())
        <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">No active waiters found.</p>
                <a href="{{ route('waiters.create') }}" class="inline-flex items-center gap-1 mt-1 text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline transition-colors">
                    Add a waiter first
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif

    <form action="{{ route('event-equipment.store') }}" method="POST" class="space-y-6" id="allocationForm">
        @csrf

        {{-- Event & Waiter Selection --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Assignment Details</h2>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Event --}}
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Event <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="event_id" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('event_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->event_name }} ({{ $event->event_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('event_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    {{-- Assign To Waiter --}}
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Assign To Waiter <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="assigned_waiter_id" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('assigned_waiter_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Waiter</option>
                                @foreach($waiters as $waiter)
                                    <option value="{{ $waiter->id }}" {{ old('assigned_waiter_id') == $waiter->id ? 'selected' : '' }}>
                                        {{ $waiter->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('assigned_waiter_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Equipment Checklist --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Select Equipment</h2>
                </div>
                <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-lg" id="selectedCount">0 selected</span>
            </div>

            <div class="p-6 space-y-6">
                @php
                    $groupedItems = $items->groupBy('category.name');
                @endphp

                @forelse($groupedItems as $categoryName => $categoryItems)
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ $categoryName }}
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($categoryItems as $item)
                            <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/30 hover:bg-amber-50/50 hover:border-amber-300 transition-all cursor-pointer group"
                                   id="item-card-{{ $item->id }}">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input type="checkbox" name="equipment[{{ $item->id }}][selected]" value="1"
                                        class="equipment-checkbox w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer"
                                        data-item-id="{{ $item->id }}"
                                        {{ old('equipment.'.$item->id.'.selected') ? 'checked' : '' }}>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 group-hover:text-amber-800 transition-colors">{{ $item->name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Serial: {{ $item->serial_code ?? 'N/A' }}</div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs font-medium {{ $item->available_quantity < 10 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $item->available_quantity }} available
                                        </span>
                                        <span class="text-xs text-slate-400">|</span>
                                        <span class="text-xs text-slate-500">Cond: {{ ucfirst($item->condition) }}</span>
                                    </div>

                                    {{-- Quantity Input (hidden until checked) --}}
                                    <div class="quantity-input mt-3 {{ old('equipment.'.$item->id.'.selected') ? '' : 'hidden' }}">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-slate-600">Qty:</span>
                                            <input type="number" name="equipment[{{ $item->id }}][quantity]" min="1" max="{{ $item->available_quantity }}"
                                                value="{{ old('equipment.'.$item->id.'.quantity', 1) }}"
                                                class="w-20 rounded-lg border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 focus:border-amber-500 focus:ring-amber-500/20 text-center">
                                        </div>
                                        @error('equipment.'.$item->id.'.quantity')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-center text-slate-500 py-8">No equipment items available.</p>
                @endforelse
            </div>

            @error('equipment')
                <div class="px-6 pb-4">
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                </div>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Notes <span class="text-slate-400 font-normal">(Optional)</span></label>
                <textarea name="notes" rows="2"
                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none"
                    placeholder="Any special instructions for this allocation...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('event-equipment.index') }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Allocate Selected Equipment
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('.equipment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const card = document.getElementById('item-card-' + itemId);
            const qtyInput = card.querySelector('.quantity-input');

            if (this.checked) {
                qtyInput.classList.remove('hidden');
                card.classList.add('bg-amber-50', 'border-amber-300');
                card.classList.remove('bg-slate-50/30', 'border-slate-200');
            } else {
                qtyInput.classList.add('hidden');
                card.classList.remove('bg-amber-50', 'border-amber-300');
                card.classList.add('bg-slate-50/30', 'border-slate-200');
            }

            updateSelectedCount();
        });
    });

    function updateSelectedCount() {
        const count = document.querySelectorAll('.equipment-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count + ' selected';
    }

    // Initialize on page load
    updateSelectedCount();
</script>
@endsection