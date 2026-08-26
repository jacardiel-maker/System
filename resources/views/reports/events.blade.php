@extends('layouts.app')
@section('title', 'Event Reports')

@section('content')
<style>
    @media print {
        @page { size: auto; margin: 15mm; }
        nav, .no-print, form { display: none !important; }
        body { background: white; height: auto !important; overflow: visible !important; }
        .print-only { display: block !important; }
        
        /* Remove overflow traps */
        .overflow-x-auto { overflow: visible !important; height: auto !important; }
        div[class*="overflow"] { overflow: visible !important; }
        
        /* Remove shadows/borders that clip content */
        .shadow-sm, .shadow-lg, .border { box-shadow: none !important; }
        
        /* Table must flow naturally and break across pages */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            display: table !important;
            page-break-inside: auto;
        }
        tr { 
            page-break-inside: avoid; 
            break-inside: avoid;
            display: table-row !important;
        }
        thead { display: table-header-group !important; }
        tbody { display: table-row-group !important; }
        td, th { 
            border: 1px solid #999 !important; 
            padding: 6px 8px !important; 
            text-align: left;
            font-size: 11px;
        }
        th { 
            background-color: #f0f0f0 !important; 
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Preserve status badge colors */
        span[class*="bg-"] { 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
            border: 1px solid #ccc !important;
        }
        
        /* Prevent large elements from being trapped on one page */
        .bg-white { background: white !important; }
    }
    .print-only { display: none; }
</style>

<!-- PRINT HEADER -->
<div class="print-only mb-6 text-center">
    <h1 class="text-3xl font-bold text-slate-800">Steph's Catering by Chef Nono</h1>
    <p class="text-slate-600 text-lg">Event Report</p>
    <p class="text-sm text-slate-500">Generated: {{ now()->format('F d, Y h:i A') }}</p>
    @if(request('from') || request('to'))
    <p class="text-sm text-slate-500">Period: {{ request('from') ?? 'All' }} to {{ request('to') ?? 'All' }}</p>
    @endif
    @if(request('status'))
    <p class="text-sm text-slate-500">Status Filter: {{ ucfirst(request('status')) }}</p>
    @endif
    <hr class="my-4 border-orange-300">
</div>

<!-- SCREEN HEADER -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Event Reports</h1>
        <p class="text-slate-400 mt-1">Filter and analyze all catering events</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
        <button onclick="window.print()" class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-2 shadow-lg shadow-orange-500/20">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- FILTER FORM -->
<div class="bg-white rounded-2xl border border-slate-100 p-6 mb-6 no-print shadow-sm">
    <form action="{{ route('reports.events') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">From Date</label>
            <input type="date" name="from" value="{{ request('from') }}" 
                class="border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">To Date</label>
            <input type="date" name="to" value="{{ request('to') }}" 
                class="border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
            <select name="status" 
                class="border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition min-w-[140px]">
                <option value="">All Statuses</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-orange-500/20 hover:shadow-xl transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('reports.events') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:text-red-600 hover:bg-red-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- TABLE - NO overflow-hidden, NO overflow-x-auto wrapper -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <table class="w-full">
        <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiters</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider no-print">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($events as $event)
            <tr class="table-row group">
                <td class="px-6 py-4">
                    <div class="font-semibold text-slate-800 group-hover:text-orange-600 transition">{{ $event->event_name }}</div>
                    <div class="text-sm text-slate-400">{{ Str::limit($event->venue_address, 30) }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-700">{{ $event->event_date->format('M d, Y') }}</div>
                    <div class="text-xs text-slate-400">{{ $event->event_date->format('h:i A') }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $event->status === 'scheduled' ? 'bg-blue-50 text-blue-600' : '' }}
                        {{ $event->status === 'ongoing' ? 'bg-purple-50 text-purple-600' : '' }}
                        {{ $event->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : '' }}
                        {{ $event->status === 'cancelled' ? 'bg-red-50 text-red-600' : '' }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-users text-slate-300 text-xs"></i>
                        <span class="font-medium">{{ $event->waiters_count }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-box text-slate-300 text-xs"></i>
                        <span class="font-medium">{{ $event->equipment_count }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-right no-print">
                    <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-orange-600 hover:text-orange-700 bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded-lg transition">
                        View <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- PRINT FOOTER -->
<div class="print-only mt-6 text-center text-sm text-slate-500">
    <hr class="my-4 border-slate-300">
    <p class="font-medium">End of Report — {{ $events->count() }} events found</p>
    <p class="mt-1">Steph's Catering by Chef Nono &copy; {{ date('Y') }}</p>
</div>
@endsection