@extends('layouts.app')
@section('title', 'Edit Allocation')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('event-equipment.index') }}" class="hover:text-amber-700 transition-colors">Allocations</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Edit Allocation</h1>
        <p class="mt-1 text-sm text-slate-500">Update equipment assignment details</p>
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

    <form action="{{ route('event-equipment.update', $eventEquipment) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Allocation Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Allocation Details</h2>
            </div>

            <div class="p-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Event <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="event_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('event_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id', $eventEquipment->event_id) == $event->id ? 'selected' : '' }}>
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

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Equipment Item <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="equipment_item_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('equipment_item_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @foreach($items as $item)
                                @php
                                    $available = $item->available_quantity + ($item->id == $eventEquipment->equipment_item_id ? $eventEquipment->quantity_allocated : 0);
                                @endphp
                                <option value="{{ $item->id }}" {{ old('equipment_item_id', $eventEquipment->equipment_item_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} ({{ $item->category->name }}) — {{ $available }} available
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('equipment_item_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Assign To Waiter <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="assigned_waiter_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('assigned_waiter_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @foreach($waiters as $waiter)
                                <option value="{{ $waiter->id }}" {{ old('assigned_waiter_id', $eventEquipment->assigned_waiter_id) == $waiter->id ? 'selected' : '' }}>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Quantity <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity_allocated" value="{{ old('quantity_allocated', $eventEquipment->quantity_allocated) }}" min="1" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('quantity_allocated') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="e.g., 10">
                        @error('quantity_allocated')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Notes <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none @error('notes') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="Any special instructions or conditions...">{{ old('notes', $eventEquipment->notes) }}</textarea>
                    @error('notes')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('event-equipment.show', $eventEquipment) }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Allocation
            </button>
        </div>
    </form>
</div>
@endsection