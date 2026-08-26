@extends('layouts.app')
@section('title', 'Log Details')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('equipment-logs.index') }}" class="hover:text-amber-700 transition-colors">Activity Logs</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Log #{{ $equipmentLog->id }}</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Activity Log Details</h1>
        <p class="mt-1 text-sm text-slate-500">Review allocation, return, or damage record</p>
    </div>

    {{-- Log Overview --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-900">Log Overview</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Action --}}
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Action</p>
                        @php
                            $actionConfig = [
                                'allocated' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated'],
                                'returned' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned'],
                                'damaged' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Damaged'],
                                'missing' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Missing'],
                            ];
                            $aConfig = $actionConfig[$equipmentLog->action] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst($equipmentLog->action)];
                        @endphp
                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $aConfig[0] }}">
                            {{ $aConfig[1] }}
                        </span>
                    </div>
                </div>

                {{-- Quantity --}}
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Quantity</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 font-bold text-slate-700">
                                {{ $equipmentLog->quantity }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Logged By --}}
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Logged By</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $equipmentLog->logger->name }}</p>
                    </div>
                </div>

                {{-- Date --}}
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Date Logged</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $equipmentLog->created_at->format('F d, Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $equipmentLog->created_at->format('h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Event & Equipment Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        {{-- Event --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Event</h2>
            </div>
            <div class="p-6">
                <p class="text-sm font-semibold text-slate-900">{{ $equipmentLog->eventEquipment->event->event_name }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $equipmentLog->eventEquipment->event->event_date->format('M d, Y') }}</p>
            </div>
        </div>

        {{-- Equipment --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Equipment</h2>
            </div>
            <div class="p-6">
                <p class="text-sm font-semibold text-slate-900">{{ $equipmentLog->eventEquipment->equipmentItem->name }}</p>
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                    {{ $equipmentLog->eventEquipment->equipmentItem->category->name }}
                </span>
            </div>
        </div>
    </div>

    {{-- Assigned Waiter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-900">Assigned Waiter</h2>
        </div>
        <div class="p-6">
            <p class="text-sm font-semibold text-slate-900">
                {{ $equipmentLog->eventEquipment->assignedWaiter->user->name ?? 'Unassigned' }}
            </p>
        </div>
    </div>

    {{-- Remarks --}}
    @if($equipmentLog->remarks)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Remarks</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-700 leading-relaxed">{{ $equipmentLog->remarks }}</p>
            </div>
        </div>
    @endif

    {{-- Back Button --}}
    <div class="pt-2">
        <a href="{{ route('equipment-logs.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Logs
        </a>
    </div>
</div>
@endsection