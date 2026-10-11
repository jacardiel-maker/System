@extends('layouts.app')
@section('title', 'Event Reports')

@section('content')
    @php
        $statusConfig = [
            'scheduled' => ['bg-blue-50 text-blue-600', 'Scheduled'],
            'ongoing'   => ['bg-purple-50 text-purple-600', 'Ongoing'],
            'completed' => ['bg-emerald-50 text-emerald-600', 'Completed'],
            'cancelled' => ['bg-red-50 text-red-600', 'Cancelled'],
        ];
        $currentStatus = strtolower((string) request('status'));
        $hasFilters = request('from') || request('to') || request('status');

        // Summary numbers (works for a collection or a paginator)
        $rows = collect($events instanceof \Illuminate\Pagination\AbstractPaginator ? $events->items() : $events);
        $statusOf = fn ($e) => strtolower(trim((string) $e->status)) ?: 'scheduled';
        $summary = [
            'total'     => $rows->count(),
            'scheduled' => $rows->filter(fn ($e) => $statusOf($e) === 'scheduled')->count(),
            'ongoing'   => $rows->filter(fn ($e) => $statusOf($e) === 'ongoing')->count(),
            'completed' => $rows->filter(fn ($e) => $statusOf($e) === 'completed')->count(),
            'cancelled' => $rows->filter(fn ($e) => $statusOf($e) === 'cancelled')->count(),
            'waiters'   => (int) $rows->sum('waiters_count'),
            'equipment' => (int) $rows->sum('equipment_count'),
        ];

        $fromLabel = request('from') ? \Carbon\Carbon::parse(request('from'))->format('M d, Y') : null;
        $toLabel = request('to') ? \Carbon\Carbon::parse(request('to'))->format('M d, Y') : null;
        $periodLabel = ($fromLabel || $toLabel)
            ? ($fromLabel ?? 'Beginning') . ' – ' . ($toLabel ?? 'Present')
            : 'All dates';

        $removeFilter = function ($key) {
            return route('reports.events', array_diff_key(request()->query(), [$key => 1]));
        };
    @endphp

    <style>
        .print-only { display: none; }

        @media print {
            @page { size: A4 portrait; margin: 12mm; }

            /* Undo the app shell (sidebar, fixed-height screen) so every page prints */
            aside, header, .no-print { display: none !important; }
            body, html { background: #fff !important; height: auto !important; overflow: visible !important; }
            .h-screen { height: auto !important; display: block !important; overflow: visible !important; }
            main { padding: 0 !important; overflow: visible !important; }
            main > .animate-fade-in { display: none !important; } /* flash messages */
            .print-only { display: block !important; }
            tfoot.print-only { display: table-footer-group !important; }
            span.print-only { display: inline !important; }

            /* Plain look on paper */
            .report-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }

            .report-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
            .report-table thead { display: table-header-group; } /* repeat header on each page */
            .report-table tr { page-break-inside: avoid; break-inside: avoid; }
            .report-table th, .report-table td {
                border: 1px solid #94a3b8 !important;
                padding: 5px 7px !important;
                color: #0f172a !important;
                vertical-align: top;
            }
            .report-table th {
                background: #f1f5f9 !important;
                font-size: 9.5px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-table .badge {
                background: none !important;
                color: #0f172a !important;
                padding: 0 !important;
                font-weight: 600;
            }

            .print-summary td {
                border: 1px solid #94a3b8;
                padding: 4px 8px;
                font-size: 10.5px;
            }

            .signature-block { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>

    {{-- ================= PRINT HEADER ================= --}}
    <div class="print-only mb-4">
        <div class="text-center">
            <h1 style="font-size:18px; font-weight:800; color:#0f172a;">Steph's Catering by Chef Nono</h1>
            <p style="font-size:13px; font-weight:600; color:#334155; margin-top:2px;">EVENT REPORT</p>
        </div>

        <table style="width:100%; margin-top:10px; font-size:10.5px; color:#334155;">
            <tr>
                <td><strong>Period:</strong> {{ $periodLabel }}</td>
                <td style="text-align:right;"><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong> {{ $statusConfig[$currentStatus][1] ?? 'All Statuses' }}</td>
                <td style="text-align:right;"><strong>Prepared by:</strong> {{ auth()->user()->name }}</td>
            </tr>
        </table>

        <table class="print-summary" style="width:100%; border-collapse:collapse; margin-top:8px;">
            <tr>
                <td><strong>Total Events:</strong> {{ $summary['total'] }}</td>
                <td><strong>Scheduled:</strong> {{ $summary['scheduled'] }}</td>
                <td><strong>Ongoing:</strong> {{ $summary['ongoing'] }}</td>
                <td><strong>Completed:</strong> {{ $summary['completed'] }}</td>
                <td><strong>Cancelled:</strong> {{ $summary['cancelled'] }}</td>
            </tr>
        </table>
        <hr style="margin-top:10px; border-color:#cbd5e1;">
    </div>

    {{-- ================= SCREEN HEADER ================= --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 no-print">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-amber-700 transition-colors">Reports</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium">Events</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Event Reports</h1>
            <p class="text-slate-400 text-sm">Filter and analyze all catering events</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('reports.index') }}"
                class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            <button type="button" onclick="window.print()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 transition">
                <i class="fas fa-print text-xs"></i> Print Report
            </button>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3 no-print">
        <form action="{{ route('reports.events') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">
            <div class="w-full lg:w-40">
                <label for="f-from" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input id="f-from" type="date" name="from" value="{{ request('from') }}" max="{{ request('to') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div class="w-full lg:w-40">
                <label for="f-to" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input id="f-to" type="date" name="to" value="{{ request('to') }}" min="{{ request('from') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div class="w-full lg:w-40">
                <label for="f-status" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select id="f-status" name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Statuses</option>
                    @foreach($statusConfig as $value => $cfg)
                        <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $cfg[1] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('reports.events') }}" title="Reset" aria-label="Reset"
                    class="flex-1 lg:flex-none lg:w-9 h-8 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-undo text-xs"></i><span class="lg:hidden">Reset</span>
                </a>
            </div>
        </form>

        @if($hasFilters)
            <div class="mt-2 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <span class="text-xs text-slate-400 font-medium">Active filters:</span>
                @if(request('from'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        From: {{ $fromLabel }}
                        <a href="{{ $removeFilter('from') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove from-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('to'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        To: {{ $toLabel }}
                        <a href="{{ $removeFilter('to') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove to-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Status: {{ $statusConfig[$currentStatus][1] ?? ucfirst($currentStatus) }}
                        <a href="{{ $removeFilter('status') }}" class="ml-1 hover:text-blue-900" aria-label="Remove status filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- ================= SUMMARY CARDS (screen) ================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mb-3 no-print">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-slate-800 leading-none">{{ $summary['total'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Total Events</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-blue-600 leading-none">{{ $summary['scheduled'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Scheduled</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-purple-600 leading-none">{{ $summary['ongoing'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Ongoing</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-emerald-600 leading-none">{{ $summary['completed'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Completed</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-red-600 leading-none">{{ $summary['cancelled'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Cancelled</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-slate-800 leading-none">{{ $summary['waiters'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Waiter Assignments</div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="report-card bg-white rounded-xl border border-slate-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="report-table w-full min-w-[720px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Venue</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiters</th>
                        <th class="px-4 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider no-print">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($events as $event)
                        @php
                            $status = $statusOf($event);
                            $cfg = $statusConfig[$status] ?? ['bg-slate-100 text-slate-600', ucfirst($status)];
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2 text-xs text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">
                                <span class="text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition">{{ $event->event_name }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500">
                                {{-- full address on paper, shortened on screen --}}
                                <span class="no-print" title="{{ $event->venue_address }}">{{ Str::limit($event->venue_address, 35) }}</span>
                                <span class="print-only">{{ $event->venue_address }}</span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="text-xs font-medium text-slate-600">{{ $event->event_date?->format('M d, Y') }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $event->event_date?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="badge inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $cfg[0] }}">{{ $cfg[1] }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-600 text-center">{{ $event->waiters_count ?? 0 }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600 text-center">{{ $event->equipment_count ?? 0 }}</td>
                            <td class="px-4 py-2 text-right no-print">
                                <a href="{{ route('events.show', $event) }}"
                                    class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 inline-flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                    title="View" aria-label="View">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-calendar-times text-2xl mb-2 text-slate-200 block no-print"></i>
                                <p class="text-xs">No events found for the selected filters.</p>
                                @if($hasFilters)
                                    <a href="{{ route('reports.events') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1 no-print">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($summary['total'] > 0)
                    <tfoot class="print-only">
                        <tr>
                            <td colspan="5" style="text-align:right; font-weight:700;">TOTAL</td>
                            <td style="text-align:center; font-weight:700;">{{ $summary['waiters'] }}</td>
                            <td style="text-align:center; font-weight:700;">{{ $summary['equipment'] }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($events instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-3 no-print">
            {{ $events->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    @endif

    {{-- ================= PRINT FOOTER ================= --}}
    <div class="print-only signature-block" style="margin-top:28px;">
        <table style="width:100%; font-size:10.5px; color:#0f172a;">
            <tr>
                <td style="width:50%; padding-right:24px;">
                    <p>Prepared by:</p>
                    <div style="margin-top:28px; border-top:1px solid #0f172a; width:220px; padding-top:3px;">
                        <strong>{{ auth()->user()->name }}</strong><br>
                        <span style="color:#475569; text-transform:capitalize;">{{ auth()->user()->role }}</span>
                    </div>
                </td>
                <td style="width:50%;">
                    <p>Noted by:</p>
                    <div style="margin-top:28px; border-top:1px solid #0f172a; width:220px; padding-top:3px;">
                        <strong>&nbsp;</strong><br>
                        <span style="color:#475569;">Owner / Manager</span>
                    </div>
                </td>
            </tr>
        </table>

        <p style="margin-top:18px; text-align:center; font-size:9.5px; color:#64748b;">
            End of Report — {{ $summary['total'] }} {{ Str::plural('event', $summary['total']) }} &middot;
            Steph's Catering by Chef Nono &copy; {{ date('Y') }}
        </p>
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