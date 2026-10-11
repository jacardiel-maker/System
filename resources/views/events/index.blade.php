@extends('layouts.app')
@section('title', 'Events')

@section('content')
    @php
        $statusStyles = [
            'scheduled' => 'bg-blue-50 text-blue-600',
            'ongoing'   => 'bg-purple-50 text-purple-600',
            'completed' => 'bg-emerald-50 text-emerald-600',
            'cancelled' => 'bg-red-50 text-red-600',
        ];
        $currentStatus = strtolower((string) request('status'));

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('events.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Events</h1>
            <p class="text-slate-400 text-sm">Manage all your catering events</p>
        </div>
        <a href="{{ route('events.create') }}"
            class="text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700 transition">
            <i class="fas fa-plus text-xs"></i> Schedule Event
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('events.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">

            {{-- Search by name --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search event name or venue..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filter by Status --}}
            <div class="w-full lg:w-36">
                <label for="f-status" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select id="f-status" name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Status</option>
                    @foreach(['scheduled' => 'Scheduled', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-full lg:w-36">
                <label for="f-from" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">From</label>
                <input id="f-from" type="date" name="from" value="{{ request('from') }}" max="{{ request('to') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            {{-- Date To --}}
            <div class="w-full lg:w-36">
                <label for="f-to" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">To</label>
                <input id="f-to" type="date" name="to" value="{{ request('to') }}" min="{{ request('from') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('events.index') }}" title="Reset" aria-label="Reset"
                    class="flex-1 lg:flex-none lg:w-9 h-8 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-undo text-xs"></i><span class="lg:hidden">Reset</span>
                </a>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if(request('search') || request('status') || request('from') || request('to'))
            <div class="mt-2 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <span class="text-xs text-slate-400 font-medium">Active filters:</span>
                @if(request('search'))
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                        Search: "{{ request('search') }}"
                        <a href="{{ $removeFilter('search') }}" class="ml-1 hover:text-amber-900" aria-label="Remove search filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Status: {{ ucfirst($currentStatus) }}
                        <a href="{{ $removeFilter('status') }}" class="ml-1 hover:text-blue-900" aria-label="Remove status filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('from'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        From: {{ \Carbon\Carbon::parse(request('from'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('from') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove from-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('to'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        To: {{ \Carbon\Carbon::parse(request('to'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('to') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove to-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Count --}}
    <div class="mb-2">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ $events->firstItem() ?? 0 }}–{{ $events->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $events->total() }}</span> events
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiters</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($events as $event)
                        @php
                            $status = strtolower(trim((string) $event->status));
                            $badge = $statusStyles[$status] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2">
                                <a href="{{ route('events.show', $event) }}"
                                    class="block text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition leading-tight">
                                    {{ $event->event_name }}</a>
                                <div class="text-xs text-slate-400 leading-tight" title="{{ $event->venue_address }}">
                                    {{ Str::limit($event->venue_address, 30) }}</div>
                            </td>
                            <td class="px-4 py-2 text-slate-500 whitespace-nowrap">
                                <span class="text-xs font-medium">{{ $event->event_date->format('M d, Y') }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $event->event_date->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $badge }}">
                                    {{ ucfirst($status ?: 'scheduled') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-users text-slate-300 text-xs"></i> {{ $event->waiters_count ?? 0 }}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-box text-slate-300 text-xs"></i> {{ $event->equipment_count ?? 0 }}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('events.show', $event) }}"
                                        class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View" aria-label="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('events.edit', $event) }}"
                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                        title="Edit" aria-label="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form id="delete-form-{{ $event->id }}" action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="openDeleteDialog({{ $event->id }}, {{ json_encode($event->event_name) }})"
                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                            title="Delete" aria-label="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-calendar-times text-2xl mb-2 text-slate-200 block"></i>
                                <p class="text-xs">No events found matching your criteria.</p>
                                @if(request('search') || request('status') || request('from') || request('to'))
                                    <a href="{{ route('events.index') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @else
                                    <a href="{{ route('events.create') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Schedule your first event</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $events->appends(request()->query())->links('pagination::tailwind') }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Event?</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Event</div>
                <div id="deleteEventName" class="font-semibold text-slate-800"></div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteDialog()"
                    class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                    Cancel
                </button>
                <button type="button" id="deleteConfirmBtn"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            let pendingDeleteId = null;
            const modal = document.getElementById('deleteModal');

            window.openDeleteDialog = function (id, name) {
                pendingDeleteId = id;
                document.getElementById('deleteEventName').textContent = name;
                modal.classList.remove('hidden');
            };

            window.closeDeleteDialog = function () {
                modal.classList.add('hidden');
                pendingDeleteId = null;
            };

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                if (!pendingDeleteId) return;
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('delete-form-' + pendingDeleteId).submit();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) window.closeDeleteDialog();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') window.closeDeleteDialog();
            });

            // Keep From <= To while picking dates
            const from = document.getElementById('f-from');
            const to = document.getElementById('f-to');
            from.addEventListener('change', () => { to.min = from.value; });
            to.addEventListener('change', () => { from.max = to.value; });
        })();
    </script>
@endsection