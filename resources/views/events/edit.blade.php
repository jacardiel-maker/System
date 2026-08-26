@extends('layouts.app')
@section('title', 'Edit Event')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('events.show', $event) }}" class="hover:text-amber-700 transition-colors">Events</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $event->event_name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Update event details and team assignments</p>
    </div>

    <form action="{{ route('events.update', $event) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Event Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Event Details</h2>
            </div>

            <div class="p-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Event Name</label>
                    <input type="text" name="event_name" value="{{ old('event_name', $event->event_name) }}" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('event_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                    @error('event_name')
                        <p class="text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Venue Address</label>
                    <textarea name="venue_address" rows="3" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none @error('venue_address') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('venue_address', $event->venue_address) }}</textarea>
                    @error('venue_address')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Event Date & Time</label>
                        <input type="datetime-local" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('event_date') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('event_date')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Setup Time</label>
                        <input type="datetime-local" name="setup_time" value="{{ old('setup_time', $event->setup_time->format('Y-m-d\TH:i')) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('setup_time') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('setup_time')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Logistics & Status Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Logistics & Status</h2>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Expected Guests</label>
                        <input type="number" name="guest_count" value="{{ old('guest_count', $event->guest_count) }}" min="1"
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all"
                            placeholder="e.g., 150">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Status</label>
                        <div class="relative">
                            <select name="status"
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer">
                                @php
                                    $statuses = [
                                        'scheduled' => ['Scheduled', 'bg-blue-50 text-blue-700'],
                                        'ongoing' => ['Ongoing', 'bg-amber-50 text-amber-700'],
                                        'completed' => ['Completed', 'bg-emerald-50 text-emerald-700'],
                                        'cancelled' => ['Cancelled', 'bg-rose-50 text-rose-700'],
                                    ];
                                @endphp
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $event->status) === $value ? 'selected' : '' }}>{{ $label[0] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none"
                        placeholder="Special requirements, dietary restrictions, setup notes...">{{ old('notes', $event->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Team Assignment Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Team Assignment</h2>
                        <p class="text-xs text-slate-500">Re-assign waiters and update their roles</p>
                    </div>
                </div>
                <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $waiters->count() }} total</span>
            </div>

            <div class="p-6">
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                    @foreach($waiters as $waiter)
                        @php
                            $existing = $event->waiters->firstWhere('id', $waiter->id);
                        @endphp
                        <label class="flex items-center gap-4 p-3 rounded-xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/30 transition-all cursor-pointer group has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/50 has-[:checked]:ring-1 has-[:checked]:ring-amber-500/20">
                            <input type="checkbox" name="waiters[{{ $loop->index }}][id]" value="{{ $waiter->id }}"
                                {{ $existing ? 'checked' : '' }}
                                class="w-5 h-5 rounded-lg border-slate-300 text-amber-600 focus:ring-amber-500/20 focus:ring-2 cursor-pointer transition-all">

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 group-hover:text-amber-800 transition-colors">{{ $waiter->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $waiter->phone }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <select name="waiters[{{ $loop->index }}][role]"
                                    class="text-sm rounded-lg border-slate-200 bg-white py-1.5 pl-3 pr-8 focus:border-amber-500 focus:ring-amber-500/20 cursor-pointer">
                                    <option value="waiter" {{ $existing && $existing->pivot->role === 'waiter' ? 'selected' : '' }}>Waiter</option>
                                    <option value="head_waiter" {{ $existing && $existing->pivot->role === 'head_waiter' ? 'selected' : '' }}>Head Waiter</option>
                                    <option value="setup_crew" {{ $existing && $existing->pivot->role === 'setup_crew' ? 'selected' : '' }}>Setup Crew</option>
                                </select>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('events.show', $event) }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Event
            </button>
        </div>
    </form>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
</style>
@endsection