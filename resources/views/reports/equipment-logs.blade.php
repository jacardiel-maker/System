@extends('layouts.app')
@section('title', 'Equipment Logs Report')

@section('content')
<style>
    @media print {
        nav, .no-print, form, aside, header, .sidebar-link { display: none !important; }
        body { background: white !important; font-family: 'Inter', sans-serif; }
        .print-only { display: block !important; }
        .print-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important; color: white !important; padding: 30px !important; border-radius: 0 !important; }
        .print-header h1 { color: white !important; font-size: 28px !important; margin: 0 !important; }
        .print-header p { color: #cbd5e1 !important; margin: 5px 0 0 !important; font-size: 14px !important; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 11px; margin-top: 20px; }
        th { background: #f8fafc !important; color: #475569 !important; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; padding: 12px 10px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:last-child td { border-bottom: 2px solid #e2e8f0; }
        .print-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-weight: 600; font-size: 10px; }
        .print-badge-allocated { background: #dbeafe; color: #1e40af; }
        .print-badge-returned { background: #d1fae5; color: #065f46; }
        .print-badge-damaged { background: #ffedd5; color: #9a3412; }
        .print-badge-missing { background: #fee2e2; color: #991b1b; }
        .print-footer { margin-top: 30px; padding-top: 15px; border-top: 2px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 11px; }
        .print-meta { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 15px 0; font-size: 12px; color: #64748b; }
        .print-meta strong { color: #334155; }
    }
    .print-only { display: none; }
</style>

{{-- Print Header --}}
<div class="print-only print-header">
    <h1><i class="fas fa-utensils mr-2"></i>Steph's Catering by Chef Nono</h1>
    <p>Equipment Activity Logs Report</p>
</div>

<div class="print-only print-meta">
    <div><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</div>
    @if(request('from') || request('to'))
    <div><strong>Period:</strong> {{ request('from') ?? 'All' }} to {{ request('to') ?? 'All' }}</div>
    @endif
    @if(request('action'))
    <div><strong>Action Filter:</strong> {{ ucfirst(request('action')) }}</div>
    @endif
</div>

{{-- Screen Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Activity Logs</h1>
        <p class="text-slate-400 mt-1">Track all equipment movements and accountability</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('reports.index') }}" class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="btn-primary text-white px-5 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

{{-- Filter Card --}}
<div class="bg-white rounded-2xl border border-slate-100 p-6 mb-6 shadow-sm no-print">
    <form action="{{ route('reports.equipment-logs') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">From Date</label>
            <input type="date" name="from" value="{{ request('from') }}" class="input-modern rounded-xl px-4 py-2.5 text-sm bg-slate-50 w-44">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">To Date</label>
            <input type="date" name="to" value="{{ request('to') }}" class="input-modern rounded-xl px-4 py-2.5 text-sm bg-slate-50 w-44">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Action</label>
            <select name="action" class="input-modern rounded-xl px-4 py-2.5 text-sm bg-slate-50 w-40">
                <option value="">All Actions</option>
                <option value="allocated" {{ request('action') === 'allocated' ? 'selected' : '' }}>Allocated</option>
                <option value="returned" {{ request('action') === 'returned' ? 'selected' : '' }}>Returned</option>
                <option value="damaged" {{ request('action') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                <option value="missing" {{ request('action') === 'missing' ? 'selected' : '' }}>Missing</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('reports.equipment-logs') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-sm font-semibold hover:bg-slate-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Logged By</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                <tr class="table-row group">
                    <td class="px-6 py-4 text-sm text-slate-500 whitespace-nowrap">
                        <div class="font-medium text-slate-700">{{ $log->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-700">
                        {{ $log->eventEquipment->event->event_name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $log->eventEquipment->equipmentItem->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs
                            {{ $log->action === 'allocated' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $log->action === 'returned' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $log->action === 'damaged' ? 'bg-orange-50 text-orange-600' : '' }}
                            {{ $log->action === 'missing' ? 'bg-red-50 text-red-600' : '' }}">
                            <i class="fas fa-{{ $log->action === 'allocated' ? 'arrow-right' : ($log->action === 'returned' ? 'check' : ($log->action === 'damaged' ? 'exclamation' : 'times')) }} mr-1 text-[10px]"></i>
                            {{ ucfirst($log->action) }}
                        </span>
                        {{-- Print-only badge --}}
                        <span class="print-only print-badge
                            {{ $log->action === 'allocated' ? 'print-badge-allocated' : '' }}
                            {{ $log->action === 'returned' ? 'print-badge-returned' : '' }}
                            {{ $log->action === 'damaged' ? 'print-badge-damaged' : '' }}
                            {{ $log->action === 'missing' ? 'print-badge-missing' : '' }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-700">{{ $log->quantity }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-600 to-slate-700 text-white flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr($log->logger->name ?? '?', 0, 1)) }}
                            </div>
                            {{ $log->logger->name ?? 'System' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate" title="{{ $log->remarks ?? '' }}">
                        {{ $log->remarks ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="text-slate-300 text-5xl mb-4"><i class="fas fa-clipboard-list"></i></div>
                        <p class="text-slate-400 font-medium">No activity logs found</p>
                        <p class="text-slate-300 text-sm mt-1">Try adjusting your filters</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Print Footer --}}
<div class="print-only print-footer">
    <p><strong>{{ $logs->count() }}</strong> log entries found</p>
    <p>Steph's Catering by Chef Nono &copy; {{ date('Y') }} | Confidential Report</p>
</div>
@endsection