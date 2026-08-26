@extends('layouts.app')
@section('title', 'Allocation Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('event-equipment.index') }}" class="hover:text-amber-700 transition-colors">Allocations</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium">Details</span>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Allocation Details</h1>
        </div>
        <div class="flex items-center gap-3">
            @if($eventEquipment->status === 'allocated')
                <a href="{{ route('event-equipment.edit', $eventEquipment) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            @endif
            @if($eventEquipment->status !== 'returned' && $eventEquipment->status !== 'lost')
                <button onclick="document.getElementById('returnModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Record Return
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Allocation Details Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Allocation Info</h2>
                            <p class="text-xs text-slate-500">{{ $eventEquipment->equipmentItem->name }}</p>
                        </div>
                    </div>
                    @php
                        $statusConfig = [
                            'returned' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned'],
                            'allocated' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated'],
                            'partially_returned' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Partially Returned'],
                            'lost' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Lost'],
                        ];
                        $config = $statusConfig[$eventEquipment->status] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst(str_replace('_', ' ', $eventEquipment->status))];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $config[0] }}">
                        {{ $config[1] }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Event</p>
                                <a href="{{ route('events.show', $eventEquipment->event) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline">{{ $eventEquipment->event->event_name }}</a>
                                <p class="text-xs text-slate-500">{{ $eventEquipment->event->event_date->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Equipment</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $eventEquipment->equipmentItem->name }}</p>
                                <p class="text-xs text-slate-500">{{ $eventEquipment->equipmentItem->category->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Assigned To</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $eventEquipment->assignedWaiter->user->name ?? 'Unassigned' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Allocated Qty</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $eventEquipment->quantity_allocated }}</p>
                            </div>
                        </div>
                    </div>

                    @if($eventEquipment->status !== 'allocated')
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">Return Breakdown</p>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                                    <div class="text-2xl font-bold text-emerald-700">{{ $eventEquipment->quantity_returned }}</div>
                                    <div class="text-xs font-medium text-emerald-600 mt-1">Returned</div>
                                </div>
                                <div class="rounded-xl bg-amber-50 border border-amber-100 p-4 text-center">
                                    <div class="text-2xl font-bold text-amber-700">{{ $eventEquipment->quantity_damaged }}</div>
                                    <div class="text-xs font-medium text-amber-600 mt-1">Damaged</div>
                                </div>
                                <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-center">
                                    <div class="text-2xl font-bold text-rose-700">{{ $eventEquipment->quantity_missing }}</div>
                                    <div class="text-xs font-medium text-rose-600 mt-1">Missing</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($eventEquipment->notes)
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $eventEquipment->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity Logs --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Activity Logs</h2>
                        <p class="text-xs text-slate-500">{{ $eventEquipment->logs->count() }} entries</p>
                    </div>
                </div>

                @if($eventEquipment->logs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">By</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($eventEquipment->logs as $log)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $logConfig = [
                                                    'allocated' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated'],
                                                    'returned' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned'],
                                                    'damaged' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Damaged'],
                                                    'missing' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Missing'],
                                                ];
                                                $lc = $logConfig[$log->action] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($log->action)];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $lc[0] }}">
                                                {{ $lc[1] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 font-semibold text-slate-700">
                                                {{ $log->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                            {{ $log->logger->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ $log->created_at->format('M d, Y') }}
                                            <span class="text-slate-300 mx-1">•</span>
                                            <span class="text-xs">{{ $log->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $log->remarks ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No activity logs</h3>
                        <p class="mt-1 text-sm text-slate-500">No actions have been recorded for this allocation.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-base font-bold text-slate-900">Quick Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if($eventEquipment->status === 'allocated')
                        <a href="{{ route('event-equipment.edit', $eventEquipment) }}"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit Allocation
                        </a>
                        <form action="{{ route('event-equipment.destroy', $eventEquipment) }}" method="POST" onsubmit="return confirm('Delete this allocation? Stock will be returned to inventory.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 hover:border-rose-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete Allocation
                            </button>
                        </form>
                    @endif
                    @if($eventEquipment->status !== 'returned' && $eventEquipment->status !== 'lost')
                        <button onclick="document.getElementById('returnModal').classList.remove('hidden')"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            Record Return
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Return Modal --}}
<div id="returnModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('returnModal').classList.add('hidden')"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Record Equipment Return</h3>
            </div>

            <form action="{{ route('event-equipment.return', $eventEquipment) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Returned (Good)</label>
                        <input type="number" name="quantity_returned" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all text-center">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Damaged</label>
                        <input type="number" name="quantity_damaged" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all text-center">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Missing</label>
                        <input type="number" name="quantity_missing" min="0" value="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 text-sm text-slate-900 focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20 transition-all text-center">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none"
                        placeholder="Condition details, issues found..."></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')"
                        class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection