@extends('layouts.app')
@section('title', 'Equipment Logs')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Activity Logs</h1>
        <p class="text-slate-400 mt-1">Track all equipment movements and accountability</p>
    </div>
</div>

{{-- Search & Filter Bar --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
    <form action="{{ route('equipment-logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
        
        {{-- Search by event or equipment --}}
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search event or equipment name..." 
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
        </div>

        {{-- Filter by Action --}}
        <div class="w-full lg:w-44">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Action</label>
            <select name="action" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                <option value="">All Actions</option>
                <option value="allocated" {{ request('action') === 'allocated' ? 'selected' : '' }}>Allocated</option>
                <option value="returned" {{ request('action') === 'returned' ? 'selected' : '' }}>Returned</option>
                <option value="damaged" {{ request('action') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                <option value="missing" {{ request('action') === 'missing' ? 'selected' : '' }}>Missing</option>
            </select>
        </div>

        {{-- Date From --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">From</label>
            <input type="date" name="from" value="{{ request('from') }}" 
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        {{-- Date To --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">To</label>
            <input type="date" name="to" value="{{ request('to') }}" 
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>
            <a href="{{ route('equipment-logs.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Reset
            </a>
        </div>
    </form>

    {{-- Active Filters Display --}}
    @if(request('search') || request('action') || request('from') || request('to'))
    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400 font-medium">Active filters:</span>
        @if(request('search'))
            <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                Search: "{{ request('search') }}" <a href="{{ route('equipment-logs.index', array_diff_key(request()->all(), ['search' => 1])) }}" class="ml-1 hover:text-amber-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('action'))
            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                Action: {{ ucfirst(request('action')) }} <a href="{{ route('equipment-logs.index', array_diff_key(request()->all(), ['action' => 1])) }}" class="ml-1 hover:text-blue-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('from'))
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                From: {{ request('from') }} <a href="{{ route('equipment-logs.index', array_diff_key(request()->all(), ['from' => 1])) }}" class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('to'))
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                To: {{ request('to') }} <a href="{{ route('equipment-logs.index', array_diff_key(request()->all(), ['to' => 1])) }}" class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
    </div>
    @endif
</div>

{{-- Results Count --}}
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500">
        Showing <span class="font-semibold text-slate-700">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $logs->total() }}</span> log entries
    </p>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Logged By</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($logs as $log)
                <tr class="table-row group hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="font-medium text-slate-700">{{ $log->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-700">
                        {{ $log->eventEquipment->event->event_name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $log->eventEquipment->equipmentItem->name }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs inline-flex items-center gap-1.5 font-medium
                            {{ $log->action === 'allocated' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $log->action === 'returned' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $log->action === 'damaged' ? 'bg-amber-50 text-amber-600' : '' }}
                            {{ $log->action === 'missing' ? 'bg-red-50 text-red-600' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full 
                                {{ $log->action === 'allocated' ? 'bg-blue-500' : '' }}
                                {{ $log->action === 'returned' ? 'bg-emerald-500' : '' }}
                                {{ $log->action === 'damaged' ? 'bg-amber-500' : '' }}
                                {{ $log->action === 'missing' ? 'bg-red-500' : '' }}">
                            </span>
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg 
                            {{ $log->action === 'allocated' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $log->action === 'returned' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $log->action === 'damaged' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $log->action === 'missing' ? 'bg-red-100 text-red-700' : '' }} font-bold text-sm">
                            {{ $log->quantity }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-600 to-slate-700 text-white flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr($log->logger->name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-slate-600">{{ $log->logger->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('equipment-logs.show', $log) }}" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 inline-flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="View Details">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                        <i class="fas fa-clipboard-list text-4xl mb-3 text-slate-200"></i>
                        <p class="text-sm">No log entries found matching your criteria.</p>
                        <a href="{{ route('equipment-logs.index') }}" class="text-amber-600 hover:underline text-sm mt-1 inline-block">Clear filters</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
</div>
@endsection