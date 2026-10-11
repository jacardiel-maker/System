@extends('layouts.app')
@section('title', 'Equipment Logs')

@section('content')
    @php
        $actionStyles = [
            'allocated' => ['badge' => 'bg-blue-50 text-blue-600',       'dot' => 'bg-blue-500',    'qty' => 'bg-blue-100 text-blue-700'],
            'returned'  => ['badge' => 'bg-emerald-50 text-emerald-600', 'dot' => 'bg-emerald-500', 'qty' => 'bg-emerald-100 text-emerald-700'],
            'damaged'   => ['badge' => 'bg-amber-50 text-amber-600',     'dot' => 'bg-amber-500',   'qty' => 'bg-amber-100 text-amber-700'],
            'missing'   => ['badge' => 'bg-red-50 text-red-600',         'dot' => 'bg-red-500',     'qty' => 'bg-red-100 text-red-700'],
        ];
        $defaultStyle = ['badge' => 'bg-slate-100 text-slate-600', 'dot' => 'bg-slate-400', 'qty' => 'bg-slate-100 text-slate-700'];

        $currentAction = strtolower((string) request('action'));
        $hasFilters = request('search') || request('action') || request('from') || request('to');

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('equipment-logs.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Activity Logs</h1>
            <p class="text-slate-400 text-sm">Track all equipment movements and accountability</p>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('equipment-logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">

            {{-- Search by event or equipment --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search event or equipment name..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filter by Action --}}
            <div class="w-full lg:w-36">
                <label for="f-action" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Action</label>
                <select id="f-action" name="action" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Actions</option>
                    @foreach(['allocated' => 'Allocated', 'returned' => 'Returned', 'damaged' => 'Damaged', 'missing' => 'Missing'] as $value => $label)
                        <option value="{{ $value }}" {{ $currentAction === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                <a href="{{ route('equipment-logs.index') }}" title="Reset" aria-label="Reset"
                    class="flex-1 lg:flex-none lg:w-9 h-8 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-undo text-xs"></i><span class="lg:hidden">Reset</span>
                </a>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if($hasFilters)
            <div class="mt-2 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <span class="text-xs text-slate-400 font-medium">Active filters:</span>
                @if(request('search'))
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                        Search: "{{ request('search') }}"
                        <a href="{{ $removeFilter('search') }}" class="ml-1 hover:text-amber-900" aria-label="Remove search filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('action'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Action: {{ ucfirst($currentAction) }}
                        <a href="{{ $removeFilter('action') }}" class="ml-1 hover:text-blue-900" aria-label="Remove action filter"><i class="fas fa-times"></i></a>
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
            Showing <span class="font-semibold text-slate-700">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $logs->total() }}</span> log entries
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Action</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Logged By</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        @php
                            $action = strtolower(trim((string) $log->action));
                            $style = $actionStyles[$action] ?? $defaultStyle;

                            $event = $log->eventEquipment->event ?? null;
                            $equipment = $log->eventEquipment->equipmentItem ?? null;
                            $loggerName = $log->logger->name ?? 'System';
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="text-xs font-medium text-slate-600">{{ $log->created_at?->format('M d, Y') ?? '—' }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $log->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-2">
                                @if($event)
                                    <a href="{{ route('events.show', $event) }}"
                                        class="text-xs font-semibold text-slate-800 hover:text-amber-600 transition leading-tight">
                                        {{ $event->event_name }}</a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Deleted event</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($equipment)
                                    <a href="{{ route('equipment-items.show', $equipment) }}"
                                        class="text-xs text-slate-600 hover:text-amber-600 transition leading-tight">
                                        {{ $equipment->name }}</a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Deleted item</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $style['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                    {{ ucfirst($action ?: 'unknown') }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md text-xs font-bold {{ $style['qty'] }}">
                                    {{ $log->quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 shrink-0 rounded-full bg-gradient-to-br from-slate-600 to-slate-700 text-white flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(mb_substr($loggerName, 0, 1)) }}
                                    </div>
                                    <span class="text-xs text-slate-600 truncate">{{ $loggerName }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('equipment-logs.show', $log) }}"
                                    class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 inline-flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                    title="View Details" aria-label="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-clipboard-list text-2xl mb-2 text-slate-200 block"></i>
                                @if($hasFilters)
                                    <p class="text-xs">No log entries found matching your criteria.</p>
                                    <a href="{{ route('equipment-logs.index') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @else
                                    <p class="text-xs">No equipment activity yet. Logs appear here once equipment is allocated or returned.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
    </div>

    <script>
        (function () {
            // Keep From <= To while picking dates
            const from = document.getElementById('f-from');
            const to = document.getElementById('f-to');
            from.addEventListener('change', () => { to.min = from.value; });
            to.addEventListener('change', () => { from.max = to.value; });
        })();
    </script>
@endsection