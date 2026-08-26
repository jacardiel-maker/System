@extends('layouts.app')
@section('title', 'Accountability Report')

@section('content')
<style>
    @media print {
        nav, .no-print, .print-hide { display: none !important; }
        body { background: white !important; }
        .print-only { display: block !important; }
        .print-break-inside { break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-card { border: 1px solid #e5e7eb; padding: 16px; margin-bottom: 12px; border-radius: 8px; }
    }
    .print-only { display: none; }
    
    /* Modern scrollbar for table */
    .table-container::-webkit-scrollbar { height: 6px; }
    .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    .table-container::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .table-container::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

{{-- Print Header --}}
<div class="print-only mb-8 text-center">
    <div class="flex items-center justify-center gap-3 mb-2">
        <i class="fas fa-utensils text-amber-700 text-4xl"></i>
        <div>
            <h1 class="text-3xl font-bold text-amber-900 tracking-tight">Steph's Catering</h1>
            <p class="text-amber-700 text-sm font-medium tracking-widest uppercase">by Chef Nono</p>
        </div>
    </div>
    <h2 class="text-xl font-semibold text-gray-800 mt-4">Staff Accountability Report</h2>
    <p class="text-sm text-gray-500 mt-1">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
    <div class="w-32 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 no-print">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Accountability Report</h1>
        <p class="text-gray-500 text-sm mt-1">Track equipment accountability across your team</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('reports.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 shadow-sm text-sm font-medium">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
        <button onclick="window.print()" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-all duration-200 shadow-lg shadow-amber-200 text-sm font-medium">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

@php
    $totalPending = 0;
    $totalDamaged = 0;
    $totalMissing = 0;
    $totalWaiters = 0;
    
    foreach($waiters as $w) {
        $p = $w->assignedEquipment->where('status', 'partially_returned')->sum('quantity_allocated');
        $d = $w->assignedEquipment->sum('quantity_damaged');
        $m = $w->assignedEquipment->sum('quantity_missing');
        if($p > 0 || $d > 0 || $m > 0) {
            $totalPending += $p;
            $totalDamaged += $d;
            $totalMissing += $m;
            $totalWaiters++;
        }
    }
    $totalIssues = $totalPending + $totalDamaged + $totalMissing;
@endphp

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Staff with Issues</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalWaiters }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Pending Returns</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $totalPending }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Damaged Items</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ $totalDamaged }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-orange-600 text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Missing Items</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $totalMissing }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600 text-lg"></i>
            </div>
        </div>
    </div>
</div>

{{-- Data Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                <i class="fas fa-clipboard-check text-amber-600"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Accountability Breakdown</h2>
                <p class="text-gray-500 text-xs">{{ $totalIssues > 0 ? $totalIssues . ' total issues found' : 'All clear' }}</p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $totalIssues > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            {{ $totalIssues > 0 ? 'Action Required' : 'All Clear' }}
        </span>
    </div>
    
    <div class="table-container overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waiter</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Damaged</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Missing</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Severity</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider no-print">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php $hasData = false; @endphp
                @foreach($waiters as $waiter)
                @php
                    $pending = $waiter->assignedEquipment->where('status', 'partially_returned')->sum('quantity_allocated');
                    $damaged = $waiter->assignedEquipment->sum('quantity_damaged');
                    $missing = $waiter->assignedEquipment->sum('quantity_missing');
                    $total = $pending + $damaged + $missing;
                    $severity = 'low';
                    if ($missing > 0) $severity = 'critical';
                    elseif ($damaged > 0) $severity = 'high';
                    elseif ($pending > 0) $severity = 'medium';
                @endphp
                @if($total > 0)
                @php $hasData = true; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors duration-150 print-break-inside">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-amber-700 font-bold text-sm shadow-sm">
                                {{ strtoupper(substr($waiter->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $waiter->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $waiter->user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($pending > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-yellow-50 text-yellow-700 text-sm font-bold border border-yellow-100">
                            <i class="fas fa-clock text-xs"></i> {{ $pending }}
                        </span>
                        @else
                        <span class="text-gray-300 text-sm">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($damaged > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700 text-sm font-bold border border-orange-100">
                            <i class="fas fa-exclamation-triangle text-xs"></i> {{ $damaged }}
                        </span>
                        @else
                        <span class="text-gray-300 text-sm">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($missing > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-sm font-bold border border-red-100">
                            <i class="fas fa-times-circle text-xs"></i> {{ $missing }}
                        </span>
                        @else
                        <span class="text-gray-300 text-sm">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($severity === 'critical')
                        <div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wide">Critical</span>
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        @elseif($severity === 'high')
                        <div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wide">High</span>
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        @elseif($severity === 'medium')
                        <div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold uppercase tracking-wide">Medium</span>
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                        @else
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide">Low</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-right no-print">
                        <a href="{{ route('waiters.show', $waiter) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition-colors text-sm font-medium">
                            <i class="fas fa-user text-xs"></i> Profile
                        </a>
                    </td>
                </tr>
                @endif
                @endforeach
                
                @if(!$hasData)
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">All Equipment Accounted For</h3>
                            <p class="text-gray-500 text-sm max-w-sm">No pending returns, damaged items, or missing equipment found across your team.</p>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    @if($hasData)
    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between no-print">
        <p class="text-xs text-gray-500">Showing {{ $totalWaiters }} staff member{{ $totalWaiters > 1 ? 's' : '' }} with accountability issues</p>
        <p class="text-xs text-gray-400">Last updated: {{ now()->diffForHumans() }}</p>
    </div>
    @endif
</div>

{{-- Print Footer --}}
<div class="print-only mt-8 text-center text-sm text-gray-500">
    <div class="w-full h-px bg-gray-300 mb-4"></div>
    <p class="font-semibold text-gray-700">End of Accountability Report</p>
    <p class="mt-1">{{ $totalIssues }} total issue{{ $totalIssues > 1 ? 's' : '' }} found across {{ $totalWaiters }} staff member{{ $totalWaiters > 1 ? 's' : '' }}</p>
    <p class="mt-2 text-amber-700 font-medium">Steph's Catering by Chef Nono &copy; {{ date('Y') }}</p>
</div>
@endsection