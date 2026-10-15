@extends('layouts.app')
@section('title', 'Equipment Usage Report')

@section('content')
    @php
        $statusConfig = [
            'allocated'          => ['bg-blue-50 text-blue-600', 'Allocated'],
            'in_use'             => ['bg-purple-50 text-purple-600', 'In Use'],
            'returned'           => ['bg-emerald-50 text-emerald-600', 'Returned'],
            'partially_returned' => ['bg-amber-50 text-amber-600', 'Partially Returned'],
            'lost'               => ['bg-red-50 text-red-600', 'Lost'],
        ];
        $hasFilters = request('from') || request('to');

        // Works for a collection or a paginator
        $rows = collect($records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->items() : $records);

        // Units still not accounted for (not yet returned, reported damaged or missing)
        $pendingOf = fn ($r) => max(0,
            (int) $r->quantity_allocated
            - (int) $r->quantity_returned
            - (int) $r->quantity_damaged
            - (int) $r->quantity_missing
        );

        $totals = [
            'records'   => $rows->count(),
            'allocated' => (int) $rows->sum('quantity_allocated'),
            'returned'  => (int) $rows->sum('quantity_returned'),
            'damaged'   => (int) $rows->sum('quantity_damaged'),
            'missing'   => (int) $rows->sum('quantity_missing'),
            'pending'   => (int) $rows->sum($pendingOf),
        ];
        $returnRate = $totals['allocated'] > 0
            ? round(($totals['returned'] / $totals['allocated']) * 100, 1)
            : 0;

        $fromLabel = request('from') ? \Carbon\Carbon::parse(request('from'))->format('M d, Y') : null;
        $toLabel = request('to') ? \Carbon\Carbon::parse(request('to'))->format('M d, Y') : null;
        $periodLabel = ($fromLabel || $toLabel)
            ? ($fromLabel ?? 'Beginning') . ' – ' . ($toLabel ?? 'Present')
            : 'All dates';

        $removeFilter = function ($key) {
            return route('reports.equipment', array_diff_key(request()->query(), [$key => 1]));
        };
    @endphp

    <style>
        .print-only { display: none; }

        @media print {
            /* Landscape: this table has many columns */
            @page { size: A4 landscape; margin: 10mm; }

            /* Undo the app shell (sidebar, fixed-height screen) so every page prints */
            aside, header, .no-print { display: none !important; }
            body, html { background: #fff !important; height: auto !important; overflow: visible !important; }
            .h-screen { height: auto !important; display: block !important; overflow: visible !important; }
            main { padding: 0 !important; overflow: visible !important; }
            main > .animate-fade-in { display: none !important; } /* flash messages */
            .print-only { display: block !important; }
            tfoot.print-only { display: table-footer-group !important; }
            span.print-only { display: inline !important; }

            .report-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
            .overflow-x-auto { overflow: visible !important; }

            .report-table { width: 100%; min-width: 0 !important; border-collapse: collapse; font-size: 10px; }
            .report-table thead { display: table-header-group; } /* repeat header on each page */
            .report-table tfoot { display: table-row-group !important; } /* TOTAL only once, at the very end */
            .report-table tr { page-break-inside: avoid; break-inside: avoid; }
            .report-table th, .report-table td {
                border: 1px solid #94a3b8 !important;
                padding: 4px 6px !important;
                color: #0f172a !important;
                vertical-align: top;
            }
            .report-table th {
                background: #f1f5f9 !important;
                font-size: 9px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-table .badge {
                background: none !important;
                color: #0f172a !important;
                padding: 0 !important;
                font-weight: 600;
            }

            .print-summary td { border: 1px solid #94a3b8; padding: 4px 8px; font-size: 10px; }
            .signature-block { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>

    {{-- ================= PRINT HEADER ================= --}}
    <div class="print-only mb-3">
        <div class="text-center">
            <h1 style="font-size:18px; font-weight:800; color:#0f172a;">Steph's Catering by Chef Nono</h1>
            <p style="font-size:13px; font-weight:600; color:#334155; margin-top:2px;">EQUIPMENT USAGE REPORT</p>
        </div>

        <table style="width:100%; margin-top:10px; font-size:10px; color:#334155;">
            <tr>
                <td><strong>Period:</strong> {{ $periodLabel }}</td>
                <td style="text-align:right;"><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Records:</strong> {{ $totals['records'] }}</td>
                <td style="text-align:right;"><strong>Prepared by:</strong> {{ auth()->user()->name }}</td>
            </tr>
        </table>

        <table class="print-summary" style="width:100%; border-collapse:collapse; margin-top:8px;">
            <tr>
                <td><strong>Allocated:</strong> {{ number_format($totals['allocated']) }}</td>
                <td><strong>Returned (Good):</strong> {{ number_format($totals['returned']) }}</td>
                <td><strong>Damaged:</strong> {{ number_format($totals['damaged']) }}</td>
                <td><strong>Missing:</strong> {{ number_format($totals['missing']) }}</td>
                <td><strong>Not Yet Returned:</strong> {{ number_format($totals['pending']) }}</td>
                <td><strong>Return Rate:</strong> {{ $returnRate }}%</td>
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
                <span class="text-slate-600 font-medium">Equipment Usage</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Usage Report</h1>
            <p class="text-slate-400 text-sm">Track equipment allocation and returns per event</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('reports.index') }}"
                class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            @include('reports.partials.export', [
                'title' => 'EQUIPMENT USAGE REPORT',
                'filename' => 'equipment-usage-report',
                'orientation' => 'landscape',
                'meta' => [
                    'Period' => $periodLabel,
                    'Records' => $totals['records'],
                ],
                'summary' => [
                    'Allocated' => number_format($totals['allocated']),
                    'Returned (Good)' => number_format($totals['returned']),
                    'Damaged' => number_format($totals['damaged']),
                    'Missing' => number_format($totals['missing']),
                    'Not Yet Returned' => number_format($totals['pending']),
                    'Return Rate' => $returnRate . '%',
                ],
            ])
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3 no-print">
        <form action="{{ route('reports.equipment') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">
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
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('reports.equipment') }}" title="Reset" aria-label="Reset"
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
            </div>
        @endif
    </div>

    {{-- ================= SUMMARY CARDS (screen) ================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 mb-3 no-print">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-slate-800 leading-none">{{ number_format($totals['allocated']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Allocated</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-emerald-600 leading-none">{{ number_format($totals['returned']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Returned (Good)</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-amber-600 leading-none">{{ number_format($totals['damaged']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Damaged</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-red-600 leading-none">{{ number_format($totals['missing']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Missing</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-blue-600 leading-none">{{ number_format($totals['pending']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Not Yet Returned</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-slate-800 leading-none">{{ $returnRate }}%</div>
            <div class="text-[11px] text-slate-500 mt-1">Return Rate</div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="report-card bg-white rounded-xl border border-slate-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="report-table w-full min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider" title="Allocated">Alloc</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider" title="Returned in good condition">Returned</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Damaged</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Missing</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider" title="Not yet returned">Pending</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($records as $record)
                        @php
                            $status = strtolower(trim((string) $record->status));
                            $cfg = $statusConfig[$status] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $status ?: 'unknown'))];
                            $pending = $pendingOf($record);
                            $event = $record->event;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2 text-xs text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">
                                @if($event)
                                    <span class="block text-xs font-semibold text-slate-800 leading-tight">{{ $event->event_name }}</span>
                                    <span class="block text-[11px] text-slate-400 leading-tight">{{ $event->event_date?->format('M d, Y') }}</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Deleted event</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-600">{{ $record->equipmentItem->name ?? 'Deleted item' }}</td>
                            <td class="px-4 py-2 text-xs {{ $record->assignedWaiter?->user ? 'text-slate-600' : 'text-slate-400 italic' }}">
                                {{ $record->assignedWaiter->user->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-slate-800">{{ (int) $record->quantity_allocated }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold text-emerald-600">{{ (int) $record->quantity_returned }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $record->quantity_damaged ? 'text-amber-600' : 'text-slate-300' }}">{{ (int) $record->quantity_damaged }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $record->quantity_missing ? 'text-red-600' : 'text-slate-300' }}">{{ (int) $record->quantity_missing }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $pending ? 'text-blue-600' : 'text-slate-300' }}">{{ $pending }}</td>
                            <td class="px-4 py-2">
                                <span class="badge inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $cfg[0] }}">{{ $cfg[1] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-box-open text-2xl mb-2 text-slate-200 block no-print"></i>
                                <p class="text-xs">No equipment records found for the selected period.</p>
                                @if($hasFilters)
                                    <a href="{{ route('reports.equipment') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1 no-print">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($totals['records'] > 0)
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-200">
                            <td colspan="4" class="px-4 py-2 text-right text-xs font-bold text-slate-600 uppercase">Total</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-slate-800">{{ number_format($totals['allocated']) }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-emerald-600">{{ number_format($totals['returned']) }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-amber-600">{{ number_format($totals['damaged']) }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-red-600">{{ number_format($totals['missing']) }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-blue-600">{{ number_format($totals['pending']) }}</td>
                            <td class="px-4 py-2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($records instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-3 no-print">
            {{ $records->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    @endif

    {{-- ================= PRINT FOOTER ================= --}}
    <div class="print-only signature-block" style="margin-top:24px;">
        <table style="width:100%; font-size:10px; color:#0f172a;">
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

        <p style="margin-top:16px; text-align:center; font-size:9px; color:#64748b;">
            End of Report — {{ $totals['records'] }} {{ Str::plural('record', $totals['records']) }} &middot;
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