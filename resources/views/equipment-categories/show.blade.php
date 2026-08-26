@extends('layouts.app')
@section('title', $equipmentCategory->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('equipment-categories.index') }}" class="hover:text-amber-700 transition-colors">Categories</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-900 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $equipmentCategory->name }}</span>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $equipmentCategory->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('equipment-categories.edit', $equipmentCategory) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Category
            </a>
            <form action="{{ route('equipment-categories.destroy', $equipmentCategory) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
        {{-- Category Info Sidebar --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-6">
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center border-b border-slate-100">
                    <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto shadow-md ring-4 ring-white">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $equipmentCategory->name }}</h2>
                    <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        {{ $equipmentCategory->code }}
                    </span>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Description</p>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $equipmentCategory->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Total Items</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $equipmentCategory->items->count() }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Low Stock Items</p>
                        <p class="text-2xl font-bold {{ $equipmentCategory->items->where('available_quantity', '<', 5)->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $equipmentCategory->items->where('available_quantity', '<', 5)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Grid --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Items in this Category</h2>
                            <p class="text-xs text-slate-500">{{ $equipmentCategory->items->count() }} equipment items</p>
                        </div>
                    </div>
                    <a href="{{ route('equipment-items.create') }}?category_id={{ $equipmentCategory->id }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </a>
                </div>

                @if($equipmentCategory->items->count() > 0)
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($equipmentCategory->items as $item)
                                @php
                                    $isLowStock = $item->available_quantity < 5;
                                    $stockPercent = $item->total_quantity > 0 ? ($item->available_quantity / $item->total_quantity) * 100 : 0;
                                @endphp
                                <div class="rounded-2xl border {{ $isLowStock ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200' }} p-5 hover:shadow-md transition-all group">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-700 transition-colors truncate">{{ $item->name }}</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $item->serial_code ?? 'No serial code' }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $item->condition === 'new' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10' }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </div>

                                    {{-- Stock Bar --}}
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-xs mb-1.5">
                                            <span class="text-slate-500">Stock Level</span>
                                            <span class="font-semibold {{ $isLowStock ? 'text-rose-700' : 'text-slate-700' }}">
                                                {{ $item->available_quantity }} / {{ $item->total_quantity }}
                                            </span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full transition-all {{ $isLowStock ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min($stockPercent, 100) }}%"></div>
                                        </div>
                                        @if($isLowStock)
                                            <p class="mt-1.5 text-xs font-medium text-rose-600 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Low stock warning
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-4 pt-4 border-t {{ $isLowStock ? 'border-rose-100' : 'border-slate-100' }}">
                                        <a href="{{ route('equipment-items.show', $item) }}"
                                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-700 hover:text-amber-800 transition-colors">
                                            View Details
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">No items yet</h3>
                        <p class="mt-1 text-sm text-slate-500">Start by adding equipment to this category.</p>
                        <a href="{{ route('equipment-items.create') }}?category_id={{ $equipmentCategory->id }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-medium text-amber-700 hover:text-amber-800 hover:underline transition-colors">
                            Add your first item
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection