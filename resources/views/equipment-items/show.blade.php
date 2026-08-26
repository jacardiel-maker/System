@extends('layouts.app')
@section('title', $equipmentItem->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('equipment-items.index') }}" class="hover:text-amber-700 transition-colors">Equipment</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $equipmentItem->name }}</span>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $equipmentItem->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('equipment-items.edit', $equipmentItem) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Item
            </a>
            <form action="{{ route('equipment-items.destroy', $equipmentItem) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 hover:border-rose-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Sidebar Info Card --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-6">
                {{-- Item Header --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center border-b border-slate-100">
                    <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $equipmentItem->name }}</h2>
                    @php
                        $conditionConfig = [
                            'new' => ['bg-emerald-100 text-emerald-700', 'New'],
                            'good' => ['bg-blue-100 text-blue-700', 'Good'],
                            'fair' => ['bg-amber-100 text-amber-700', 'Fair'],
                            'poor' => ['bg-rose-100 text-rose-700', 'Poor'],
                        ];
                        $cConfig = $conditionConfig[$equipmentItem->condition] ?? ['bg-slate-100 text-slate-600', ucfirst($equipmentItem->condition)];
                    @endphp
                    <span class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-semibold {{ $cConfig[0] }}">
                        {{ $cConfig[1] }}
                    </span>
                </div>

                {{-- Details --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Category</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $equipmentItem->category->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Serial Code</p>
                            <p class="text-sm font-semibold text-slate-700 font-mono">{{ $equipmentItem->serial_code ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Quantity</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $equipmentItem->total_quantity }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Available</p>
                            <p class="text-sm font-semibold {{ $equipmentItem->available_quantity < 5 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $equipmentItem->available_quantity }}
                                @if($equipmentItem->available_quantity < 5)
                                    <span class="text-xs font-normal text-rose-400 ml-1">(Low stock)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Allocation History</h2>
                            <p class="text-xs text-slate-500">{{ $equipmentItem->eventAllocations->count() }} total allocations</p>
                        </div>
                    </div>
                </div>

                @if($equipmentItem->eventAllocations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waiter</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($equipmentItem->eventAllocations as $alloc)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                            {{ $alloc->event->event_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $alloc->assignedWaiter->user->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 font-semibold text-slate-700">
                                                {{ $alloc->quantity_allocated }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($alloc->status === 'returned')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Returned
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ ucfirst(str_replace('_', ' ', $alloc->status)) }}
                                                </span>
                                            @endif
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
                        <h3 class="text-sm font-semibold text-slate-900">No allocations yet</h3>
                        <p class="mt-1 text-sm text-slate-500">This item hasn't been allocated to any events.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection