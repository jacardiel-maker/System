@extends('layouts.app')
@section('title', 'Equipment Logs Report')

@section('content')
    @php
        $actionConfig = [
            'allocated' => ['bg-blue-50 text-blue-600', 'Allocated', 'fa-arrow-right'],
            'returned'  => ['bg-emerald-50 text-emerald-600', 'Returned', 'fa-check'],
            'damaged'   => ['bg-orange-50 text-orange-600', 'Damaged', 'fa-exclamation'],
            'missing'   => ['bg-red-50 text-red-600', 'Missing', 'fa-times'],
        ];
        $currentAction = strtolower((string) request('action'));
        $hasFilters = request('from') || request('to') || request('action');

        // Works for a collection or a paginator
        $rows = collect($logs instanceof \Illuminate\Pagination\AbstractPaginator ? $logs->items() : $logs);
        $actionOf = fn ($l) => strtolower(trim((string) $l->action));

        // Units per action (e.g. 120 allocated, 110 returned, 6 damaged, 4 missing)
        $summary = ['entries' => $rows->count()];
        foreach (array_keys($actionConfig) as $a) {
            $summary[$a] = (int) $rows->filter(fn ($l) => $actionOf($l) === $a)->sum('quantity');
        }

        $fromLabel = request('from') ? \Carbon\Carbon::parse(request('from'))->format('M d, Y') : null;
        $toLabel = request('to') ? \Carbon\Carbon::parse(request('to'))->format('M d, Y') : null;
        $periodLabel = ($fromLabel || $toLabel)
            ? ($fromLabel ?? 'Beginning') . ' – ' . ($toLabel ?? 'Present')
            : 'All dates';

        $removeFilter = function ($key) {
            return route('reports.equipment-logs', array_diff_key(request()->query(), [$key => 1]));
        };
    @endphp

    <style>
        .print-only { display: none; }

        @media print {
            /* Landscape: this table has many columns and long remarks */
            @page { size: A4 landscape; margin: 10mm; }

            /* Undo the app shell (sidebar, fixed-height screen) so every page prints */
            aside, header, .no-print { display: none !important; }
            body, html { background: #fff !important; height: auto !important; overflow: visible !important; }
            .h-screen { height: auto !important; display: block !important; overflow: visible !important; }
            main { padding: 0 !important; overflow: visible !important; }
            main > .animate-fade-in { display: none !important; } /* flash messages */
            .print-only { display: block !important; }
            span.print-only { display: inline !important; }

            .report-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
            .overflow-x-auto { overflow: visible !important; }

            .report-table { width: 100%; min-width: 0 !important; border-collapse: collapse; font-size: 10px; }
            .report-table thead { display: table-header-group; } /* repeat header on each page */
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
            .report-table .badge i { display: none !important; }

            .print-summary td { border: 1px solid #94a3b8; padding: 4px 8px; font-size: 10px; }
            .signature-block { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>

    {{-- ================= PRINT HEADER ================= --}}
    <div class="print-only mb-3">
        <div class="text-center">
            <h1 style="font-size:18px; font-weight:800; color:#0f172a;">Steph's Catering by Chef Nono</h1>
            <p style="font-size:13px; font-weight:600; color:#334155; margin-top:2px;">EQUIPMENT ACTIVITY LOGS REPORT</p>
        </div>

        <table style="width:100%; margin-top:10px; font-size:10px; color:#334155;">
            <tr>
                <td><strong>Period:</strong> {{ $periodLabel }}</td>
                <td style="text-align:right;"><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Action:</strong> {{ $actionConfig[$currentAction][1] ?? 'All Actions' }}</td>
                <td style="text-align:right;"><strong>Prepared by:</strong> {{ auth()->user()->name }}</td>
            </tr>
        </table>

        <table class="print-summary" style="width:100%; border-collapse:collapse; margin-top:8px;">
            <tr>
                <td><strong>Log Entries:</strong> {{ number_format($summary['entries']) }}</td>
                <td><strong>Units Allocated:</strong> {{ number_format($summary['allocated']) }}</td>
                <td><strong>Units Returned:</strong> {{ number_format($summary['returned']) }}</td>
                <td><strong>Units Damaged:</strong> {{ number_format($summary['damaged']) }}</td>
                <td><strong>Units Missing:</strong> {{ number_format($summary['missing']) }}</td>
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
                <span class="text-slate-600 font-medium">Equipment Logs</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Activity Logs</h1>
            <p class="text-slate-400 text-sm">Track all equipment movements and accountability</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('reports.index') }}"
                class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            @include('reports.partials.export', [
                'title' => 'EQUIPMENT ACTIVITY LOGS REPORT',
                'filename' => 'equipment-logs-report',
                'orientation' => 'landscape',
                'meta' => [
                    'Period' => $periodLabel,
                    'Action' => $actionConfig[$currentAction][1] ?? 'All Actions',
                ],
                'summary' => [
                    'Log Entries' => number_format($summary['entries']),
                    'Units Allocated' => number_format($summary['allocated']),
                    'Units Returned' => number_format($summary['returned']),
                    'Units Damaged' => number_format($summary['damaged']),
                    'Units Missing' => number_format($summary['missing']),
                ],
            ])
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3 no-print">
        <form action="{{ route('reports.equipment-logs') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">
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
                <label for="f-action" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Action</label>
                <select id="f-action" name="action" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Actions</option>
                    @foreach($actionConfig as $value => $cfg)
                        <option value="{{ $value }}" {{ $currentAction === $value ? 'selected' : '' }}>{{ $cfg[1] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('reports.equipment-logs') }}" title="Reset" aria-label="Reset"
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
                @if(request('action'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Action: {{ $actionConfig[$currentAction][1] ?? ucfirst($currentAction) }}
                        <a href="{{ $removeFilter('action') }}" class="ml-1 hover:text-blue-900" aria-label="Remove action filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- ================= SUMMARY CARDS (screen) ================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-3 no-print">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-slate-800 leading-none">{{ number_format($summary['entries']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Log Entries</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-blue-600 leading-none">{{ number_format($summary['allocated']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Units Allocated</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-emerald-600 leading-none">{{ number_format($summary['returned']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Units Returned</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-orange-600 leading-none">{{ number_format($summary['damaged']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Units Damaged</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2">
            <div class="text-lg font-bold text-red-600 leading-none">{{ number_format($summary['missing']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Units Missing</div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="report-card bg-white rounded-xl border border-slate-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="report-table w-full min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Action</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Logged By</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        @php
                            $action = $actionOf($log);
                            $cfg = $actionConfig[$action] ?? ['bg-slate-100 text-slate-600', ucfirst($action ?: 'unknown'), 'fa-circle'];
                            $loggerName = $log->logger->name ?? 'System';
                            $remarks = trim((string) ($log->remarks ?? ''));
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2 text-xs text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="text-xs font-medium text-slate-600">{{ $log->created_at?->format('M d, Y') ?? '—' }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $log->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs font-semibold text-slate-800">
                                {{ $log->eventEquipment->event->event_name ?? 'Deleted event' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-600">
                                {{ $log->eventEquipment->equipmentItem->name ?? 'Deleted item' }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $cfg[0] }}">
                                    <i class="fas {{ $cfg[2] }} text-[9px]"></i> {{ $cfg[1] }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-slate-800">{{ (int) $log->quantity }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600 whitespace-nowrap">{{ $loggerName }}</td>
                            <td class="px-4 py-2 text-xs text-slate-500">
                                @if($remarks !== '')
                                    {{-- shortened on screen, full text on paper --}}
                                    <span class="no-print block max-w-[220px] truncate" title="{{ $remarks }}">{{ $remarks }}</span>
                                    <span class="print-only">{{ $remarks }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-clipboard-list text-2xl mb-2 text-slate-200 block no-print"></i>
                                <p class="text-xs">No activity logs found for the selected filters.</p>
                                @if($hasFilters)
                                    <a href="{{ route('reports.equipment-logs') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1 no-print">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-3 no-print">
            {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
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
            End of Report — {{ $summary['entries'] }} log {{ Str::plural('entry', $summary['entries']) }} &middot;
            Steph's Catering by Chef Nono &copy; {{ date('Y') }} &middot; Confidential
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