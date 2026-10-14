@extends('layouts.app')
@section('title', 'Log Details')

@section('content')
@php
    $allocation = $equipmentLog->eventEquipment;
    $event      = $allocation?->event;
    $item       = $allocation?->equipmentItem;

    $action = strtolower(trim((string) $equipmentLog->action));
    $actionConfig = [
        'allocated' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated', 'fa-box', 'bg-blue-50 text-blue-600'],
        'returned'  => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned', 'fa-undo', 'bg-emerald-50 text-emerald-600'],
        'damaged'   => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Damaged', 'fa-tools', 'bg-amber-50 text-amber-600'],
        'missing'   => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Missing', 'fa-question-circle', 'bg-rose-50 text-rose-600'],
    ];
    $ac = $actionConfig[$action]
        ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst(str_replace('_', ' ', $action ?: 'unknown')), 'fa-circle', 'bg-slate-100 text-slate-500'];

    $qty = (int) $equipmentLog->quantity;
@endphp

<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                <a href="{{ route('equipment-logs.index') }}" class="hover:text-amber-700 transition-colors">Equipment Logs</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-slate-700 font-medium">Log #{{ $equipmentLog->id }}</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Log Details</h1>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {{ $ac[0] }}">
                    <i class="fas {{ $ac[2] }} text-[9px]"></i> {{ $ac[1] }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Allocation, return, or damage record</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('equipment-logs.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            @if($allocation)
                <a href="{{ route('event-equipment.show', $allocation) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                    <i class="fas fa-hand-holding text-xs"></i> View Allocation
                </a>
            @endif
        </div>
    </div>

    {{-- Summary strip --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg {{ $ac[3] }} flex items-center justify-center shrink-0">
                <i class="fas {{ $ac[2] }} text-sm"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Action</div>
                <div class="text-sm font-bold text-slate-900">{{ $ac[1] }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <i class="fas fa-hashtag text-sm"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Quantity</div>
                <div class="text-sm font-bold text-slate-900">{{ $qty }} {{ \Illuminate\Support\Str::plural('unit', $qty) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <i class="fas fa-user-edit text-sm"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Logged By</div>
                <div class="text-sm font-bold text-slate-900 truncate">{{ $equipmentLog->logger->name ?? 'System' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <i class="fas fa-clock text-sm"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Date Logged</div>
                <div class="text-sm font-bold text-slate-900">{{ optional($equipmentLog->created_at)->format('M d, Y') ?? '—' }}</div>
                <div class="text-[11px] text-slate-500">{{ optional($equipmentLog->created_at)->format('h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="fas fa-clipboard-list text-xs"></i>
            </div>
            <h2 class="text-sm font-bold text-slate-900">Record Details</h2>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 divide-slate-100">
            {{-- Event --}}
            <div class="px-4 py-3 sm:border-b sm:border-r border-slate-100">
                <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Event</dt>
                <dd class="mt-0.5">
                    @if($event)
                        <a href="{{ route('events.show', $event) }}"
                            class="text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline">{{ $event->event_name }}</a>
                        <div class="text-xs text-slate-500">{{ optional($event->event_date)->format('M d, Y') ?? '—' }}</div>
                    @else
                        <span class="text-sm text-slate-400 italic">Deleted event</span>
                    @endif
                </dd>
            </div>

            {{-- Equipment --}}
            <div class="px-4 py-3 sm:border-b border-slate-100">
                <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Equipment</dt>
                <dd class="mt-0.5">
                    @if($item)
                        <a href="{{ route('equipment-items.show', $item) }}"
                            class="text-sm font-semibold text-slate-900 hover:text-amber-700">{{ $item->name }}</a>
                        <div>
                            <span class="inline-flex mt-0.5 px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                {{ $item->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                    @else
                        <span class="text-sm text-slate-400 italic">Deleted item</span>
                    @endif
                </dd>
            </div>

            {{-- Assigned waiter --}}
            <div class="px-4 py-3 sm:border-r border-slate-100">
                <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Assigned Waiter</dt>
                <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ $allocation?->assignedWaiter?->user?->name ?? 'Unassigned' }}
                </dd>
            </div>

            {{-- Allocation --}}
            <div class="px-4 py-3">
                <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Allocation</dt>
                <dd class="mt-0.5 text-sm text-slate-900">
                    @if($allocation)
                        <a href="{{ route('event-equipment.show', $allocation) }}"
                            class="font-semibold text-amber-700 hover:text-amber-800 hover:underline">#{{ $allocation->id }}</a>
                        <span class="text-xs text-slate-500">— {{ (int) $allocation->quantity_allocated }} allocated</span>
                    @else
                        <span class="text-slate-400 italic">Deleted allocation</span>
                    @endif
                </dd>
            </div>
        </dl>

        {{-- Remarks --}}
        <div class="px-4 py-3 border-t border-slate-100">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Remarks</div>
            @if($equipmentLog->remarks)
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $equipmentLog->remarks }}</p>
            @else
                <p class="text-sm text-slate-400 italic">No remarks</p>
            @endif
        </div>
    </div>
</div>
@endsection