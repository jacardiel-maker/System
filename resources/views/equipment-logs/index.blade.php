@extends('layouts.app')
@section('title', 'Equipment Logs')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Activity Logs</h1>
        <p class="text-slate-400 mt-1">Track all equipment movements and accountability</p>
    </div>
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
                @foreach($logs as $log)
                <tr class="table-row group">
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
                        <span class="status-badge px-3 py-1 rounded-full text-xs inline-flex items-center gap-1.5
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $logs->links('pagination::tailwind') }}
</div>
@endsection