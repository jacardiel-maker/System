@extends('layouts.app')
@section('title', 'Accountability Report')

@section('content')
    @php
        // Units still out for a record: allocated minus what was returned, reported damaged or missing.
        // Only counts for records that are not yet closed (returned / lost).
        $pendingOf = function ($r) {
            $status = strtolower(trim((string) $r->status));
            if (in_array($status, ['returned', 'lost'])) {
                return 0;
            }
            return max(0,
                (int) $r->quantity_allocated
                - (int) $r->quantity_returned
                - (int) $r->quantity_damaged
                - (int) $r->quantity_missing
            );
        };

        // Build one row per waiter who has at least one issue
        $rowsData = collect($waiters)->map(function ($waiter) use ($pendingOf) {
            $equipment = $waiter->assignedEquipment ?? collect();

            $pending = (int) $equipment->sum($pendingOf);
            $damaged = (int) $equipment->sum('quantity_damaged');
            $missing = (int) $equipment->sum('quantity_missing');
            $total = $pending + $damaged + $missing;

            // Items involved, e.g. "Dinner Plate (2 missing, 1 damaged)"
            $items = $equipment->map(function ($r) use ($pendingOf) {
                $parts = [];
                if ($p = $pendingOf($r)) $parts[] = "{$p} pending";
                if ($d = (int) $r->quantity_damaged) $parts[] = "{$d} damaged";
                if ($m = (int) $r->quantity_missing) $parts[] = "{$m} missing";
                if (!$parts) return null;
                $name = $r->equipmentItem->name ?? 'Deleted item';
                return $name . ' (' . implode(', ', $parts) . ')';
            })->filter()->values();

            $severity = $missing > 0 ? 'critical' : ($damaged > 0 ? 'high' : ($pending > 0 ? 'medium' : 'low'));

            return (object) compact('waiter', 'pending', 'damaged', 'missing', 'total', 'items', 'severity');
        })->filter(fn ($row) => $row->total > 0)
          ->sortByDesc(fn ($row) => [$row->missing, $row->damaged, $row->pending]) // most serious first
          ->values();

        $totals = [
            'waiters' => $rowsData->count(),
            'pending' => (int) $rowsData->sum('pending'),
            'damaged' => (int) $rowsData->sum('damaged'),
            'missing' => (int) $rowsData->sum('missing'),
        ];
        $totalIssues = $totals['pending'] + $totals['damaged'] + $totals['missing'];

        $severityConfig = [
            'critical' => ['bg-red-100 text-red-700', 'Critical', 'bg-red-500', '100%'],
            'high'     => ['bg-orange-100 text-orange-700', 'High', 'bg-orange-500', '75%'],
            'medium'   => ['bg-yellow-100 text-yellow-700', 'Medium', 'bg-yellow-500', '50%'],
            'low'      => ['bg-green-100 text-green-700', 'Low', 'bg-green-500', '25%'],
        ];
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

            .report-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
            .overflow-x-auto { overflow: visible !important; }

            .report-table { width: 100%; min-width: 0 !important; border-collapse: collapse; font-size: 10.5px; }
            .report-table thead { display: table-header-group; } /* repeat header on each page */
            .report-table tfoot { display: table-row-group !important; } /* TOTAL only once, at the end */
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
                font-weight: 700;
            }
            .report-table .bar { display: none !important; }

            .print-summary td { border: 1px solid #94a3b8; padding: 4px 8px; font-size: 10.5px; }
            .signature-block { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>

    {{-- ================= PRINT HEADER ================= --}}
    <div class="print-only mb-4">
        <div class="text-center">
            <h1 style="font-size:18px; font-weight:800; color:#0f172a;">Steph's Catering by Chef Nono</h1>
            <p style="font-size:13px; font-weight:600; color:#334155; margin-top:2px;">STAFF ACCOUNTABILITY REPORT</p>
        </div>

        <table style="width:100%; margin-top:10px; font-size:10.5px; color:#334155;">
            <tr>
                <td><strong>Status:</strong> {{ $totalIssues > 0 ? 'Action Required' : 'All Clear' }}</td>
                <td style="text-align:right;"><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Staff with Issues:</strong> {{ $totals['waiters'] }}</td>
                <td style="text-align:right;"><strong>Prepared by:</strong> {{ auth()->user()->name }}</td>
            </tr>
        </table>

        <table class="print-summary" style="width:100%; border-collapse:collapse; margin-top:8px;">
            <tr>
                <td><strong>Pending Returns:</strong> {{ $totals['pending'] }}</td>
                <td><strong>Damaged Items:</strong> {{ $totals['damaged'] }}</td>
                <td><strong>Missing Items:</strong> {{ $totals['missing'] }}</td>
                <td><strong>Total Issues:</strong> {{ $totalIssues }}</td>
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
                <span class="text-slate-600 font-medium">Accountability</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Accountability Report</h1>
            <p class="text-slate-400 text-sm">Track equipment accountability across your team</p>
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

    {{-- ================= SUMMARY CARDS (screen) ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-3 no-print">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2 flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-users text-sm"></i></div>
            <div>
                <div class="text-lg font-bold text-slate-800 leading-none">{{ $totals['waiters'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Staff with Issues</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2 flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center"><i class="fas fa-clock text-sm"></i></div>
            <div>
                <div class="text-lg font-bold text-yellow-600 leading-none">{{ $totals['pending'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Pending Returns</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2 flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-sm"></i></div>
            <div>
                <div class="text-lg font-bold text-orange-600 leading-none">{{ $totals['damaged'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Damaged Items</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-3 py-2 flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-lg bg-red-50 text-red-600 flex items-center justify-center"><i class="fas fa-times-circle text-sm"></i></div>
            <div>
                <div class="text-lg font-bold text-red-600 leading-none">{{ $totals['missing'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Missing Items</div>
            </div>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="report-card bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2 no-print">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Accountability Breakdown</h2>
                <p class="text-xs text-slate-400">{{ $totalIssues > 0 ? $totalIssues . ' total ' . Str::plural('issue', $totalIssues) . ' found' : 'All clear' }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $totalIssues > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                {{ $totalIssues > 0 ? 'Action Required' : 'All Clear' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="report-table w-full min-w-[820px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider w-10">#</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Items Involved</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Damaged</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Missing</th>
                        <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Severity</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider no-print">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rowsData as $row)
                        @php
                            $w = $row->waiter;
                            $name = $w->user->name ?? 'Unknown';
                            $sev = $severityConfig[$row->severity];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2 text-xs text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2.5">
                                    @if($w->photo)
                                        <img src="{{ asset('storage/' . $w->photo) }}" alt="{{ $name }}"
                                            class="w-8 h-8 shrink-0 rounded-full object-cover bg-slate-100 no-print">
                                    @else
                                        <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-amber-700 font-bold text-xs no-print">
                                            {{ strtoupper(mb_substr($name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $name }}</p>
                                        <p class="text-[11px] text-slate-400 leading-tight">{{ $w->phone ?: ($w->user->email ?? '') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-[11px] text-slate-600 leading-snug">
                                @foreach($row->items as $itemLine)
                                    <div>{{ $itemLine }}</div>
                                @endforeach
                            </td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $row->pending ? 'text-yellow-600' : 'text-slate-300' }}">{{ $row->pending ?: '—' }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $row->damaged ? 'text-orange-600' : 'text-slate-300' }}">{{ $row->damaged ?: '—' }}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold {{ $row->missing ? 'text-red-600' : 'text-slate-300' }}">{{ $row->missing ?: '—' }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="badge inline-block px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide {{ $sev[0] }}">{{ $sev[1] }}</span>
                                <div class="bar w-14 h-1 bg-slate-100 rounded-full overflow-hidden mx-auto mt-1">
                                    <div class="h-full rounded-full {{ $sev[2] }}" style="width: {{ $sev[3] }}"></div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right no-print">
                                <a href="{{ route('waiters.show', $w) }}"
                                    class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 inline-flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                    title="View Profile" aria-label="View Profile">
                                    <i class="fas fa-user text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-2 no-print">
                                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-800">All Equipment Accounted For</p>
                                <p class="text-xs text-slate-500 mt-1">No pending returns, damaged items, or missing equipment found across your team.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($totals['waiters'] > 0)
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-200">
                            <td colspan="3" class="px-4 py-2 text-right text-xs font-bold text-slate-600 uppercase">Total</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-yellow-600">{{ $totals['pending'] }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-orange-600">{{ $totals['damaged'] }}</td>
                            <td class="px-3 py-2 text-xs text-center font-bold text-red-600">{{ $totals['missing'] }}</td>
                            <td class="px-3 py-2"></td>
                            <td class="px-4 py-2 no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($totals['waiters'] > 0)
            <div class="px-4 py-2 bg-slate-50/50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 no-print">
                <p class="text-[11px] text-slate-500">
                    Showing {{ $totals['waiters'] }} staff {{ Str::plural('member', $totals['waiters']) }} with accountability issues, most serious first.
                </p>
                <p class="text-[11px] text-slate-400">
                    <span class="font-semibold">Severity:</span> Critical = has missing · High = has damaged · Medium = pending only
                </p>
            </div>
        @endif
    </div>

    {{-- ================= PRINT FOOTER ================= --}}
    <div class="print-only signature-block" style="margin-top:28px;">
        <p style="font-size:9.5px; color:#475569; margin-bottom:14px;">
            Severity: Critical = has missing items · High = has damaged items · Medium = has pending returns only.
        </p>
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
            End of Report — {{ $totalIssues }} total {{ Str::plural('issue', $totalIssues) }} across {{ $totals['waiters'] }} staff {{ Str::plural('member', $totals['waiters']) }} &middot;
            Steph's Catering by Chef Nono &copy; {{ date('Y') }}
        </p>
    </div>
@endsection