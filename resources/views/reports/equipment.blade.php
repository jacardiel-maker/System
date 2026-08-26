@extends('layouts.app')
@section('title', 'Equipment Usage Report')

@section('content')
<style>
    @media print {
        nav, .no-print, form { display: none !important; }
        body { background: white; }
        .print-only { display: block !important; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
    }
    .print-only { display: none; }
</style>

<div class="print-only mb-6 text-center">
    <h1 class="text-3xl font-bold text-amber-800">Steph's Catering by Chef Nono</h1>
    <p class="text-gray-600 text-lg">Equipment Usage Report</p>
    <p class="text-sm text-gray-500">Generated: {{ now()->format('F d, Y h:i A') }}</p>
    @if(request('from') || request('to'))
    <p class="text-sm text-gray-500">
        Period: {{ request('from') ?? 'All' }} to {{ request('to') ?? 'All' }}
    </p>
    @endif
    <hr class="my-4 border-amber-300">
</div>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Usage Report</h1>
        <p class="text-slate-400 mt-1">Track equipment allocation and returns per event</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-2 shadow-lg shadow-orange-500/20">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 p-6 mb-6 no-print shadow-sm">
    <form action="{{ route('reports.equipment') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">From Date</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border-2 border-slate-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition bg-slate-50">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">To Date</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border-2 border-slate-200 px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition bg-slate-50">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-orange-500/20">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('reports.equipment') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-sm font-medium hover:bg-slate-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Alloc</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Ret</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Dmg</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Miss</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($records as $record)
                <tr class="table-row group">
                    <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $record->event->event_name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $record->equipmentItem->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $record->assignedWaiter->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $record->quantity_allocated }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-emerald-600">{{ $record->quantity_returned }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-orange-600">{{ $record->quantity_damaged }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-red-600">{{ $record->quantity_missing }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs
                            {{ $record->status === 'returned' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $record->status === 'allocated' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $record->status === 'partially_returned' ? 'bg-orange-50 text-orange-600' : '' }}
                            {{ $record->status === 'lost' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="print-only mt-6 text-center text-sm text-gray-500">
    <hr class="my-4 border-gray-300">
    <p>End of Report — {{ $records->count() }} records found</p>
    <p>Steph's Catering by Chef Nono &copy; {{ date('Y') }}</p>
</div>
@endsection