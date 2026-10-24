@extends('layouts.app')
@section('title', 'Waiters')

@section('content')
    @php
        $statusStyles = [
            'active'   => 'bg-emerald-50 text-emerald-600',
            'inactive' => 'bg-slate-100 text-slate-500',
        ];
        $currentStatus = strtolower((string) request('status'));

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('waiters.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };

        // 'calendar' (default) or 'list'
        $view = request('view') === 'list' ? 'list' : 'calendar';

        // Toggle links keep the current search/status filters
        $viewLink = function ($v) {
            return route('waiters.index', array_merge(array_diff_key(request()->query(), ['page' => 1]), ['view' => $v]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Waiters</h1>
            <p class="text-slate-400 text-sm">Manage your catering staff</p>
        </div>
        <div class="flex items-center gap-2 self-start">
            {{-- View toggle --}}
            <div class="inline-flex rounded-lg border border-slate-200 bg-white overflow-hidden text-xs font-semibold">
                <a href="{{ $viewLink('calendar') }}"
                    class="px-3 py-2 inline-flex items-center gap-1.5 transition {{ $view === 'calendar' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-calendar-alt"></i> Calendar
                </a>
                <a href="{{ $viewLink('list') }}"
                    class="px-3 py-2 inline-flex items-center gap-1.5 transition {{ $view === 'list' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-list"></i> List
                </a>
            </div>
            <a href="{{ route('waiters.create') }}"
                class="text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 transition">
                <i class="fas fa-plus text-xs"></i> Add Waiter
            </a>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('waiters.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">
            <input type="hidden" name="view" value="{{ $view }}">

            {{-- Search by name/email/phone --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search name, email or phone..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filter by Status --}}
            <div class="w-full lg:w-36">
                <label for="f-status" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select id="f-status" name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Status</option>
                    @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                        <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($view === 'list')
            {{-- Date Hired From --}}
            <div class="w-full lg:w-36">
                <label for="f-from" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Hired From</label>
                <input id="f-from" type="date" name="from" value="{{ request('from') }}" max="{{ request('to') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            {{-- Date Hired To --}}
            <div class="w-full lg:w-36">
                <label for="f-to" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Hired To</label>
                <input id="f-to" type="date" name="to" value="{{ request('to') }}" min="{{ request('from') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            @endif

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('waiters.index', ['view' => $view]) }}" title="Reset" aria-label="Reset"
                    class="flex-1 lg:flex-none lg:w-9 h-8 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-undo text-xs"></i><span class="lg:hidden">Reset</span>
                </a>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if(request('search') || request('status') || ($view === 'list' && (request('from') || request('to'))))
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
                @if($view === 'list' && request('from'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        Hired from: {{ \Carbon\Carbon::parse(request('from'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('from') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove hired-from filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if($view === 'list' && request('to'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        Hired to: {{ \Carbon\Carbon::parse(request('to'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('to') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove hired-to filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    @if($view === 'list')
    {{-- ===================== LIST VIEW (original table) ===================== --}}
    {{-- Results Count --}}
    <div class="mb-2">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ $waiters->firstItem() ?? 0 }}–{{ $waiters->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $waiters->total() }}</span> waiters
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($waiters as $waiter)
                        @php
                            $name = $waiter->user->name ?? 'Unknown';
                            $email = $waiter->user->email ?? '—';
                            $status = strtolower(trim((string) $waiter->status));
                            $badge = $statusStyles[$status] ?? 'bg-slate-100 text-slate-500';
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2.5">
                                    @if($waiter->photo)
                                        <img src="{{ asset('storage/' . $waiter->photo) }}" alt="{{ $name }}"
                                            class="w-8 h-8 shrink-0 rounded-lg object-cover bg-slate-100">
                                    @else
                                        <div class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ strtoupper(mb_substr($name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('waiters.show', $waiter) }}"
                                            class="block text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition leading-tight truncate">
                                            {{ $name }}</a>
                                        <div class="text-xs text-slate-400 leading-tight truncate">{{ $email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500 whitespace-nowrap">
                                @if($waiter->phone)
                                    <a href="tel:{{ $waiter->phone }}" class="hover:text-amber-600 transition inline-flex items-center gap-1">
                                        <i class="fas fa-phone text-slate-300 text-xs"></i> {{ $waiter->phone }}
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $badge }}">
                                    {{ ucfirst($status ?: 'inactive') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs font-medium text-slate-500 whitespace-nowrap">
                                {{ $waiter->date_hired?->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('waiters.show', $waiter) }}"
                                        class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View" aria-label="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('waiters.edit', $waiter) }}"
                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                        title="Edit" aria-label="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form id="delete-form-{{ $waiter->id }}" action="{{ route('waiters.destroy', $waiter) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="openDeleteDialog({{ $waiter->id }}, {{ json_encode($name) }})"
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
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-users text-2xl mb-2 text-slate-200 block"></i>
                                <p class="text-xs">No waiters found matching your criteria.</p>
                                @if(request('search') || request('status') || request('from') || request('to'))
                                    <a href="{{ route('waiters.index', ['view' => 'list']) }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @else
                                    <a href="{{ route('waiters.create') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Add your first waiter</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $waiters->appends(request()->query())->links('pagination::tailwind') }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Waiter?</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Waiter</div>
                <div id="deleteItemName" class="font-semibold text-slate-800"></div>
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
                document.getElementById('deleteItemName').textContent = name;
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

            // Keep Hired From <= Hired To while picking dates
            const from = document.getElementById('f-from');
            const to = document.getElementById('f-to');
            from.addEventListener('change', () => { to.min = from.value; });
            to.addEventListener('change', () => { from.max = to.value; });
        })();
    </script>
@else
        {{-- ===================== CALENDAR VIEW ===================== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 sm:p-5">
            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-xs font-medium text-slate-400 mr-1">Each card is an event. The dot beside a waiter shows if they confirmed:</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#059669"></span> Confirmed</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#d97706"></span> Pending</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600"><span class="w-2.5 h-2.5 rounded-full" style="background:#dc2626"></span> Declined</span>
            </div>

            <div id="calendar"></div>
        </div>

        <style>
            /* Google-Calendar-style look for the FullCalendar views */
            #calendar {
                --fc-border-color: #e8eaed;
                --fc-page-bg-color: #fff;
                --fc-neutral-bg-color: #f8f9fa;
                --fc-today-bg-color: transparent;
                --fc-now-indicator-color: #ea4335;
                --fc-event-border-color: transparent;
                --fc-event-text-color: #fff;
                --fc-more-link-bg-color: transparent;
                --fc-more-link-text-color: #3c4043;
                --fc-list-event-hover-bg-color: #f8f9fa;
                --fc-button-bg-color: #fff;
                --fc-button-border-color: #dadce0;
                --fc-button-text-color: #3c4043;
                --fc-button-hover-bg-color: #f1f3f4;
                --fc-button-hover-border-color: #dadce0;
                --fc-button-active-bg-color: #fef3c7;
                --fc-button-active-border-color: #fcd34d;
                font-family: inherit;
                font-size: 13px;
                color: #3c4043;
            }

            /* ---- Toolbar: [Today] ( < > ) Month Year ............ ( Month | Week | Schedule ) ---- */
            #calendar .fc-header-toolbar { margin-bottom: 1rem; gap: .5rem; }
            #calendar .fc-toolbar-chunk { display: flex; align-items: center; gap: 4px; }
            #calendar .fc-toolbar-title { margin: 0 0 0 .5rem; font-size: 1.375rem; font-weight: 400; color: #3c4043; }
            #calendar .fc-button { height: 2.25rem; padding: 0 1rem; border-radius: 9999px; font-size: .875rem; font-weight: 500; text-transform: none; box-shadow: none !important; }
            #calendar .fc-today-button:disabled { opacity: .55; }
            #calendar .fc-prev-button, #calendar .fc-next-button { width: 2.25rem; padding: 0; margin-left: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 9999px; background: transparent; color: #5f6368; }
            #calendar .fc-prev-button:hover, #calendar .fc-next-button:hover { background: #f1f3f4; color: #202124; }
            #calendar .fc-toolbar-chunk:last-child .fc-button-group { overflow: hidden; border: 1px solid #dadce0; border-radius: 9999px; background: #fff; }
            #calendar .fc-toolbar-chunk:last-child .fc-button { margin-left: 0; border: 0; border-radius: 0; }
            #calendar .fc-toolbar-chunk:last-child .fc-button + .fc-button { border-left: 1px solid #dadce0; }
            #calendar .fc-button-primary:not(:disabled).fc-button-active,
            #calendar .fc-button-primary:not(:disabled):active { background: #fef3c7; color: #92400e; }

            /* ---- Grid ---- */
            #calendar .fc-theme-standard .fc-scrollgrid { border: 0; }
            #calendar .fc-theme-standard td, #calendar .fc-theme-standard th { border-color: #e8eaed; }
            #calendar .fc-col-header-cell-cushion { padding: 8px 4px; font-size: 11px; font-weight: 500; letter-spacing: .8px; text-transform: uppercase; color: #70757a; text-decoration: none; }

            /* Month: centered day numbers, today = filled circle */
            #calendar .fc-daygrid-day-frame { min-height: 6.75rem; }
            #calendar .fc-daygrid-day-top { justify-content: center; }
            #calendar .fc-daygrid-day-number { width: 1.75rem; height: 1.75rem; margin: 4px 0 2px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; font-size: 12px; font-weight: 500; color: #3c4043; text-decoration: none; }
            #calendar .fc-day-other .fc-daygrid-day-number { color: #9aa0a6; }
            #calendar .fc-day-today .fc-daygrid-day-number { background: #d97706; color: #fff; }

            /* ---- Events ---- */
            #calendar .fc-event { border-radius: 4px; cursor: pointer; font-weight: 500; transition: box-shadow .12s, filter .12s; }
            #calendar .fc-event:hover { filter: brightness(.94); box-shadow: 0 1px 3px rgba(60, 64, 67, .35); }
            #calendar .fc-daygrid-block-event { margin: 1px 4px; padding: 1px 6px; font-size: 12px; line-height: 1.5; }
            #calendar .fc-daygrid-block-event .fc-event-time { margin-right: 4px; font-weight: 500; opacity: .9; }
            #calendar .fc-more-link { margin: 0 6px; font-size: 11px; font-weight: 600; color: #3c4043; }
            #calendar .fc-more-link:hover { text-decoration: underline; background: transparent; }
            .fc-popover { border: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 28px rgba(60, 64, 67, .28); }
            .fc-popover-header { background: #fff; border-bottom: 1px solid #f1f3f4; font-weight: 500; color: #3c4043; }

            /* ---- Week view: small weekday over a big day number, hour labels, red "now" line ---- */
            #calendar .fc-timegrid .fc-col-header-cell-cushion { padding: 0; }
            #calendar .gc-head { padding: 6px 0 8px; text-align: center; }
            #calendar .gc-dow { font-size: 11px; font-weight: 500; letter-spacing: .8px; text-transform: uppercase; color: #70757a; }
            #calendar .gc-dom { width: 2.75rem; height: 2.75rem; margin: 2px auto 0; border-radius: 9999px; line-height: 2.75rem; font-size: 1.5rem; font-weight: 400; color: #3c4043; }
            #calendar .gc-dom-today { background: #d97706; color: #fff; }
            #calendar .fc-timegrid-slot { height: 1.5rem; border-color: #e8eaed; }
            #calendar .fc-timegrid-slot-minor { border-top-color: transparent; }
            #calendar .fc-timegrid-slot-label-cushion { padding: 0 8px; font-size: 10px; font-weight: 500; text-transform: uppercase; color: #70757a; }
            #calendar .fc-timegrid-event { border-radius: 6px; box-shadow: 0 0 0 1px #fff; }
            #calendar .fc-timegrid-event .fc-event-main { padding: 3px 6px; font-size: 12px; }
            #calendar .fc-timegrid-now-indicator-line { border-width: 2px 0 0; }
            #calendar .fc-timegrid-now-indicator-arrow { width: 12px; height: 12px; margin: -6px 0 0 -6px; border: 0; border-radius: 9999px; background: #ea4335; }

            /* ---- Schedule (list) view ---- */
            #calendar .fc-list { border: 0; }
            #calendar .fc-list-day-cushion { padding: 12px 16px; display: flex; gap: 12px; background: #fff; }
            #calendar .fc-list-day-text, #calendar .fc-list-day-side-text { font-size: 13px; font-weight: 500; color: #3c4043; text-decoration: none; }
            #calendar .fc-list-event td { padding: 10px 16px; border-color: #f1f3f4; }
            #calendar .fc-list-event-time { color: #70757a; }
            #calendar .fc-list-event-title { font-weight: 500; color: #202124; }
            #calendar .fc-list-empty { background: #fff; color: #70757a; }

            /* ---- Event card with its waiters listed inside (one card per event) ---- */
            #calendar .fc-event.gc-card { background: #fffbeb; border: 0; border-left: 4px solid #d97706; color: #3c4043; }
            #calendar .fc-event.gc-card:hover { filter: none; background: #fef3c7; }
            #calendar .fc-daygrid-block-event.gc-card { margin: 2px 4px; padding: 3px 6px 4px; }
            #calendar .gc-ev { white-space: normal; line-height: 1.35; }
            #calendar .gc-ev-title { font-weight: 600; color: #202124; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            #calendar .gc-ev-time { margin-right: 4px; font-weight: 500; color: #70757a; }
            #calendar .gc-ev-sub { font-size: 11px; color: #70757a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            #calendar .gc-ws { margin-top: 2px; }
            #calendar .gc-w { display: flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 500; color: #3c4043; }
            #calendar .gc-w span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            #calendar .gc-dot { flex: none; width: 7px; height: 7px; border-radius: 9999px; }
            #calendar .fc-list-event .gc-ev-title { white-space: normal; }

            @media (max-width: 640px) {
                #calendar .fc-header-toolbar { flex-direction: column; align-items: stretch; }
                #calendar .fc-toolbar-chunk { justify-content: space-between; }
                #calendar .fc-toolbar-title { font-size: 1.125rem; }
                #calendar .fc-toolbar-chunk:last-child .fc-button-group { width: 100%; }
                #calendar .fc-toolbar-chunk:last-child .fc-button { flex: 1; }
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const confirmationColors = { confirmed: '#059669', pending: '#d97706', declined: '#dc2626' };
                const roleLabels = { waiter: 'Waiter', head_waiter: 'Head Waiter', setup_crew: 'Setup Crew' };

                // Week view header like Google Calendar: small weekday over a big day number
                function weekHeader(arg) {
                    const box = document.createElement('div');
                    box.className = 'gc-head';

                    const dow = document.createElement('div');
                    dow.className = 'gc-dow';
                    dow.textContent = arg.date.toLocaleDateString('en-US', { weekday: 'short' });

                    const dom = document.createElement('div');
                    dom.className = 'gc-dom' + (arg.isToday ? ' gc-dom-today' : '');
                    dom.textContent = arg.date.getDate();

                    box.appendChild(dow);
                    box.appendChild(dom);
                    return { domNodes: [box] };
                }

                const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    initialView: window.innerWidth < 640 ? 'listMonth' : 'dayGridMonth',
                    headerToolbar: {
                        left: 'today prev,next title',
                        center: '',
                        right: 'dayGridMonth,timeGridWeek,listMonth'
                    },
                    buttonText: { today: 'Today', month: 'Month', week: 'Week', list: 'Schedule' },
                    height: 'auto',
                    firstDay: 0,
                    fixedWeekCount: false,          // only show the weeks the month needs
                    nowIndicator: true,
                    eventDisplay: 'block',
                    displayEventEnd: false,
                    dayMaxEvents: false,            // never hide an event behind "+N more"
                    allDaySlot: false,
                    slotDuration: '00:30:00',
                    slotLabelFormat: { hour: 'numeric', meridiem: 'short' },
                    slotMinTime: '06:00:00',
                    slotMaxTime: '24:00:00',
                    scrollTime: '08:00:00',
                    eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', omitZeroMinute: true },
                    noEventsContent: 'No waiter assignments found.',
                    views: {
                        dayGridMonth: { dayHeaderFormat: { weekday: 'short' } },
                        timeGridWeek: { dayHeaderContent: weekHeader, height: 680 }
                    },

                    // Loads only the visible range, and respects the search/status filters.
                    events: {
                        url: @json(route('waiters.feed')),
                        extraParams: function () {
                            return {
                                search: @json((string) request('search')),
                                status: @json((string) request('status')),
                            };
                        },
                        failure: function () { alert('Could not load the calendar. Please refresh the page.'); }
                    },

                    eventClassNames: function () { return ['gc-card']; },

                    // Event name on top, then every waiter assigned to it (dot = confirmed / pending / declined).
                    eventContent: function (arg) {
                        const p = arg.event.extendedProps || {};
                        const waiters = Array.isArray(p.waiters) ? p.waiters : [];

                        const box = document.createElement('div');
                        box.className = 'gc-ev';

                        const title = document.createElement('div');
                        title.className = 'gc-ev-title';
                        if (arg.timeText) {
                            const t = document.createElement('span');
                            t.className = 'gc-ev-time';
                            t.textContent = arg.timeText;
                            title.appendChild(t);
                        }
                        title.appendChild(document.createTextNode(arg.event.title));
                        box.appendChild(title);

                        if (p.customer) {
                            const sub = document.createElement('div');
                            sub.className = 'gc-ev-sub';
                            sub.textContent = p.customer;
                            box.appendChild(sub);
                        }

                        const list = document.createElement('div');
                        list.className = 'gc-ws';
                        waiters.forEach(function (w) {
                            const row = document.createElement('div');
                            row.className = 'gc-w';
                            row.title = (roleLabels[w.role] || w.role || 'Waiter') + ' — ' + w.confirmation;

                            const dot = document.createElement('span');
                            dot.className = 'gc-dot';
                            dot.style.background = confirmationColors[w.confirmation] || '#64748b';

                            const name = document.createElement('span');
                            name.textContent = w.name;

                            row.appendChild(dot);
                            row.appendChild(name);
                            list.appendChild(row);
                        });
                        box.appendChild(list);

                        return { domNodes: [box] };
                    },

                    eventDidMount: function (info) {
                        const p = info.event.extendedProps || {};
                        const waiters = Array.isArray(p.waiters) ? p.waiters : [];
                        info.el.title = info.event.title
                            + (p.customer ? ' (' + p.customer + ')' : '')
                            + (p.venue ? ' @ ' + p.venue : '')
                            + '\n' + waiters.map(function (w) {
                                return '• ' + w.name + ' — ' + (roleLabels[w.role] || w.role || 'Waiter') + ', ' + w.confirmation;
                            }).join('\n');
                    }
                });

                calendar.render();
            });
        </script>
    @endif
@endsection