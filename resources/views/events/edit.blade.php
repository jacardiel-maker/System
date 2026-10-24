@extends('layouts.app')
@section('title', 'Edit Event')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('events.index') }}" class="hover:text-amber-700 transition-colors">Events</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('events.show', $event) }}" class="hover:text-amber-700 transition-colors truncate max-w-[200px]">{{ $event->event_name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $event->event_name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Update event details and team assignments</p>
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
                    <label class="block text-sm font-semibold text-slate-700">Event Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="event_name" value="{{ old('event_name', $event->event_name) }}" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('event_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                    @error('event_name')
                        <p class="text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Customer --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">
                        Customer <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $event->customer_name) }}" required maxlength="255"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('customer_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="Who ordered the catering? e.g., Reyes family (birthday), ABC Corp. HR">
                    @error('customer_name')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Venue Address <span class="text-rose-500">*</span></label>
                    <textarea name="venue_address" rows="3" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none @error('venue_address') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('venue_address', $event->venue_address) }}</textarea>
                    @error('venue_address')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Event Date & Time <span class="text-rose-500">*</span></label>
                        <input id="event_date" type="datetime-local" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d\TH:i')) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('event_date') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('event_date')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Setup Time <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="setup_time" value="{{ old('setup_time', $event->setup_time?->format('Y-m-d\TH:i')) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('setup_time') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('setup_time')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Other events on the chosen day (filled in by the availability script) --}}
                <div id="dayEventsPanel" class="hidden"></div>
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
                                        'scheduled' => 'Scheduled',
                                        'ongoing'   => 'Ongoing',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ];
                                    $currentStatus = strtolower((string) old('status', $event->status));
                                @endphp
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
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
        @php
            // After a validation error, use what the user had picked; otherwise use the saved assignments.
            $hasOldInput = old('_token') !== null;
            $oldWaiters = collect(old('waiters', []))->filter(fn ($w) => !empty($w['id']))->keyBy('id');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
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
                @if($waiters->count() > 0)
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span id="waiterSelectedCount" class="text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">0 selected</span>
                        <span class="hidden sm:inline text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $waiters->count() }} total</span>
                    </div>
                @endif
            </div>

            <div class="p-6">
                @if($waiters->count() > 0)
                    {{-- Search waiter by name --}}
                    <div class="flex flex-col sm:flex-row gap-2 mb-3">
                        <div class="relative flex-1">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="waiterSearch" placeholder="Search waiter by name..." autocomplete="off"
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 pl-9 pr-9 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all">
                            <button type="button" id="waiterSearchClear" aria-label="Clear search"
                                class="hidden absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-600 px-3 py-2 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 select-none">
                            <input type="checkbox" id="waiterShowSelected" class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500/20">
                            Show selected only
                        </label>
                    </div>

                    <div id="busyWarning" class="hidden mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800"></div>

                    <div id="waiterList" class="space-y-2 max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                        @foreach($waiters as $waiter)
                            @php
                                $wName = $waiter->user->name ?? 'Unknown';
                                $existing = $event->waiters->firstWhere('id', $waiter->id);

                                if ($hasOldInput) {
                                    $pick = $oldWaiters->get((string) $waiter->id) ?? $oldWaiters->get($waiter->id);
                                    $isChecked = (bool) $pick;
                                    $role = $pick['role'] ?? ($existing->pivot->role ?? 'waiter');
                                } else {
                                    $isChecked = (bool) $existing;
                                    $role = $existing->pivot->role ?? 'waiter';
                                }
                            @endphp
                            <label data-waiter-id="{{ $waiter->id }}" data-assigned="{{ $existing ? '1' : '0' }}" data-waiter-label="{{ $wName }}" data-waiter-name="{{ strtolower($wName) }}"
                                class="waiter-row flex items-center gap-4 p-3 rounded-xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/30 transition-all cursor-pointer group has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/50 has-[:checked]:ring-1 has-[:checked]:ring-amber-500/20">
                                <input type="checkbox" name="waiters[{{ $loop->index }}][id]" value="{{ $waiter->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    class="waiter-check w-5 h-5 rounded-lg border-slate-300 text-amber-600 focus:ring-amber-500/20 focus:ring-2 cursor-pointer transition-all">

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 group-hover:text-amber-800 transition-colors truncate">
                                        {{ $wName }}
                                        @if($existing)
                                            <span class="ml-1 align-middle text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Assigned</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $waiter->phone ?: '—' }}</p>
                                    <p class="waiter-avail mt-0.5 text-[11px] text-slate-400"></p>
                                </div>

                                <div class="flex-shrink-0">
                                    <select name="waiters[{{ $loop->index }}][role]"
                                        class="text-sm rounded-lg border-slate-200 bg-white py-1.5 pl-3 pr-8 focus:border-amber-500 focus:ring-amber-500/20 cursor-pointer">
                                        <option value="waiter" {{ $role === 'waiter' ? 'selected' : '' }}>Waiter</option>
                                        <option value="head_waiter" {{ $role === 'head_waiter' ? 'selected' : '' }}>Head Waiter</option>
                                        <option value="setup_crew" {{ $role === 'setup_crew' ? 'selected' : '' }}>Setup Crew</option>
                                    </select>
                                </div>
                            </label>
                        @endforeach

                        <div id="waiterNoResults" class="hidden text-center py-6 text-sm text-slate-500">
                            No waiter matches "<span id="waiterNoResultsTerm" class="font-semibold text-slate-700"></span>".
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-sm text-slate-500">No active waiters found.</p>
                        <a href="{{ route('waiters.create') }}" class="inline-flex items-center mt-2 text-sm font-medium text-amber-700 hover:text-amber-800 hover:underline">
                            Add a waiter first
                        </a>
                    </div>
                @endif
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

<script>
    (function () {
        const search = document.getElementById('waiterSearch');
        if (!search) return; // no waiters on the page

        const clearBtn = document.getElementById('waiterSearchClear');
        const showSelected = document.getElementById('waiterShowSelected');
        const rows = Array.from(document.querySelectorAll('.waiter-row'));
        const checks = Array.from(document.querySelectorAll('.waiter-check'));
        const countEl = document.getElementById('waiterSelectedCount');
        const noResults = document.getElementById('waiterNoResults');
        const noResultsTerm = document.getElementById('waiterNoResultsTerm');

        function applyFilter() {
            const term = search.value.trim().toLowerCase();
            const onlySelected = showSelected.checked;
            let visible = 0;

            rows.forEach(row => {
                const matchesName = !term || row.dataset.waiterName.includes(term);
                const isChecked = row.querySelector('.waiter-check').checked;
                const show = matchesName && (!onlySelected || isChecked);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            clearBtn.classList.toggle('hidden', !term);
            noResults.classList.toggle('hidden', visible > 0);
            noResultsTerm.textContent = term || (onlySelected ? 'selected' : '');
        }

        function updateCount() {
            const n = checks.filter(c => c.checked).length;
            countEl.textContent = n + ' selected';
        }

        search.addEventListener('input', applyFilter);
        // Pressing Enter in the search box should not submit the whole event form
        search.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });

        clearBtn.addEventListener('click', () => {
            search.value = '';
            applyFilter();
            search.focus();
        });

        showSelected.addEventListener('change', applyFilter);

        checks.forEach(c => c.addEventListener('change', () => {
            updateCount();
            if (showSelected.checked) applyFilter();
        }));

        updateCount();
        applyFilter();
    })();
</script>
{{-- Waiter availability for the chosen event date --}}
<script>
    (function () {
        const dateInput = document.getElementById('event_date');
        if (!dateInput) return;

        const panel = document.getElementById('dayEventsPanel');
        const warning = document.getElementById('busyWarning');
        const rows = Array.from(document.querySelectorAll('.waiter-row'));
        const url = @json(route('events.availability'));
        const excludeId = @json($event->id);
        const roleLabels = { waiter: 'Waiter', head_waiter: 'Head Waiter', setup_crew: 'Setup Crew' };

        let busy = {};
        let loadedDay = null;
        let seq = 0;

        function make(tag, className, text) {
            const node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined) node.textContent = text;
            return node;
        }

        function prettyDay(day) {
            return new Date(day + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function setRowMessage(row, text, colorClass) {
            const el = row.querySelector('.waiter-avail');
            if (!el) return;
            el.textContent = text;
            el.className = 'waiter-avail mt-0.5 text-[11px] ' + colorClass;
        }

        function setRowLocked(row, locked) {
            const check = row.querySelector('.waiter-check');
            const select = row.querySelector('select');
            if (!check) return;
            const wasChecked = check.checked;
            if (locked) check.checked = false;
            check.disabled = locked;       // disabled inputs are not submitted
            if (select) select.disabled = locked;
            row.style.opacity = locked ? '0.6' : '';
            row.style.cursor = locked ? 'not-allowed' : '';
            if (locked && wasChecked) check.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function renderWarning() {
            if (!warning) return;
            const clashing = rows.filter(r => {
                const check = r.querySelector('.waiter-check');
                return check && check.checked && (busy[r.dataset.waiterId] || []).length;
            });
            if (!clashing.length) {
                warning.classList.add('hidden');
                warning.textContent = '';
                return;
            }
            const names = clashing.map(r => r.dataset.waiterLabel || 'A waiter');
            warning.textContent = 'Heads up: ' + names.join(', ') + (names.length > 1 ? ' are' : ' is')
                + ' also assigned to another event that day. Please double-check.';
            warning.classList.remove('hidden');
        }

        function renderRows(state) {
            rows.forEach(row => {
                if (state !== 'ready') setRowLocked(row, false);
                if (state === 'none') return setRowMessage(row, 'Pick the event date to check availability', 'text-slate-400');
                if (state === 'loading') return setRowMessage(row, 'Checking availability...', 'text-slate-400');
                if (state === 'error') return setRowMessage(row, 'Could not check availability', 'text-slate-400');

                const jobs = busy[row.dataset.waiterId] || [];
                if (jobs.length) {
                    const text = jobs.map(j =>
                        j.event_name + ' (' + j.time + ', ' + (roleLabels[j.role] || j.role) + ', ' + j.confirmation + ')'
                    ).join('; ');
                    // Waiters already on another event that day cannot be picked again.
                    // (On the edit page, someone already assigned to THIS event stays selectable so they are not dropped.)
                    const locked = row.dataset.assigned !== '1';
                    setRowLocked(row, locked);
                    setRowMessage(row,
                        (locked ? 'Not available. Already assigned to: ' : 'Also assigned that day: ') + text,
                        locked ? 'text-rose-600 font-medium' : 'text-amber-700 font-medium');
                } else {
                    setRowLocked(row, false);
                    setRowMessage(row, 'Available', 'text-emerald-600 font-medium');
                }
            });
            renderWarning();
        }

        function renderPanel(day, events) {
            panel.replaceChildren();
            if (!events.length) {
                panel.className = 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800';
                panel.textContent = 'No other events on ' + prettyDay(day) + '. All active waiters are free that day.';
                return;
            }
            panel.className = 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900';
            panel.appendChild(make('p', 'font-semibold mb-1.5',
                events.length + ' other ' + (events.length > 1 ? 'events' : 'event') + ' on ' + prettyDay(day) + ':'));

            const list = make('ul', 'space-y-1');
            events.forEach(ev => {
                const li = make('li', 'text-xs');
                const link = make('a', 'font-semibold underline hover:text-amber-700', ev.name);
                link.href = ev.url;
                link.target = '_blank';
                link.rel = 'noopener';
                li.appendChild(link);
                li.appendChild(document.createTextNode(
                    ' · ' + ev.time
                    + (ev.customer ? ' · Customer: ' + ev.customer : '')
                    + ' · ' + ev.waiter_count + (ev.waiter_count === 1 ? ' waiter' : ' waiters')
                ));
                list.appendChild(li);
            });
            panel.appendChild(list);
        }

        async function load() {
            const value = dateInput.value;
            if (!value) {
                loadedDay = null;
                busy = {};
                panel.classList.add('hidden');
                renderRows('none');
                return;
            }

            const day = value.slice(0, 10);
            if (day === loadedDay) return;

            const mySeq = ++seq;
            renderRows('loading');

            try {
                const params = new URLSearchParams({ date: day });
                if (excludeId) params.set('exclude', excludeId);

                const res = await fetch(url + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (mySeq !== seq) return; // a newer date was picked meanwhile

                busy = data.busy || {};
                loadedDay = day;
                renderPanel(day, data.events || []);
                panel.classList.remove('hidden');
                renderRows('ready');
            } catch (e) {
                if (mySeq !== seq) return;
                busy = {};
                loadedDay = null;
                panel.classList.add('hidden');
                renderRows('error');
            }
        }

        dateInput.addEventListener('change', load);
        document.querySelectorAll('.waiter-check').forEach(c => c.addEventListener('change', renderWarning));

        load();
    })();
</script>
@endsection