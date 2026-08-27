@extends('layouts.app')
@section('title', 'Equipment Inventory')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Inventory</h1>
        <p class="text-slate-400 mt-1">Track all catering equipment and stock levels</p>
    </div>
    <a href="{{ route('equipment-items.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700">
        <i class="fas fa-plus"></i> Add Item
    </a>
</div>

{{-- Search & Filter Bar --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
    <form action="{{ route('equipment-items.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
        
        {{-- Search by name or serial --}}
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search item name or serial code..." 
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
        </div>

        {{-- Filter by Category --}}
        <div class="w-full lg:w-48">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Category</label>
            <select name="category" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                <option value="">All Categories</option>
                @foreach(\App\Models\EquipmentCategory::all() as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filter by Condition --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Condition</label>
            <select name="condition" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                <option value="">All</option>
                <option value="new" {{ request('condition') === 'new' ? 'selected' : '' }}>New</option>
                <option value="good" {{ request('condition') === 'good' ? 'selected' : '' }}>Good</option>
                <option value="fair" {{ request('condition') === 'fair' ? 'selected' : '' }}>Fair</option>
                <option value="damaged" {{ request('condition') === 'damaged' ? 'selected' : '' }}>Damaged</option>
            </select>
        </div>

        {{-- Filter by Stock Level --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Stock</label>
            <select name="stock" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                <option value="">All Stock</option>
                <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                <option value="available" {{ request('stock') === 'available' ? 'selected' : '' }}>Available</option>
            </select>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>
            <a href="{{ route('equipment-items.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Reset
            </a>
        </div>
    </form>

    {{-- Active Filters Display --}}
    @if(request('search') || request('category') || request('condition') || request('stock'))
    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400 font-medium">Active filters:</span>
        @if(request('search'))
            <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                Search: "{{ request('search') }}" <a href="{{ route('equipment-items.index', array_diff_key(request()->all(), ['search' => 1])) }}" class="ml-1 hover:text-amber-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('category'))
            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                Category: {{ \App\Models\EquipmentCategory::find(request('category'))?->name ?? 'Unknown' }} <a href="{{ route('equipment-items.index', array_diff_key(request()->all(), ['category' => 1])) }}" class="ml-1 hover:text-blue-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('condition'))
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                Condition: {{ ucfirst(request('condition')) }} <a href="{{ route('equipment-items.index', array_diff_key(request()->all(), ['condition' => 1])) }}" class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('stock'))
            <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                Stock: {{ ucfirst(request('stock')) }} <a href="{{ route('equipment-items.index', array_diff_key(request()->all(), ['stock' => 1])) }}" class="ml-1 hover:text-purple-900"><i class="fas fa-times"></i></a>
            </span>
        @endif
    </div>
    @endif
</div>

{{-- Results Count --}}
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500">
        Showing <span class="font-semibold text-slate-700">{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $items->total() }}</span> items
    </p>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Serial</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Condition</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($items as $item)
                <tr class="table-row group {{ $item->available_quantity < 5 ? 'bg-red-50/30' : '' }} hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 group-hover:text-amber-600 transition">{{ $item->name }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">{{ $item->category->name }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400 font-mono">{{ $item->serial_code ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 rounded-full bg-slate-100 overflow-hidden">
                                @php $pct = $item->total_quantity > 0 ? ($item->available_quantity / $item->total_quantity) * 100 : 0; @endphp
                                <div class="h-full rounded-full {{ $pct < 20 ? 'bg-red-500' : ($pct < 50 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-sm font-semibold {{ $item->available_quantity < 5 ? 'text-red-600' : 'text-slate-600' }}">{{ $item->available_quantity }}/{{ $item->total_quantity }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs font-medium {{ $item->condition === 'new' ? 'bg-emerald-50 text-emerald-600' : ($item->condition === 'good' ? 'bg-blue-50 text-blue-600' : ($item->condition === 'fair' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')) }}">
                            {{ ucfirst($item->condition) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('equipment-items.show', $item) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('equipment-items.edit', $item) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form action="{{ route('equipment-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        <i class="fas fa-box text-4xl mb-3 text-slate-200"></i>
                        <p class="text-sm">No equipment items found matching your criteria.</p>
                        <a href="{{ route('equipment-items.index') }}" class="text-amber-600 hover:underline text-sm mt-1 inline-block">Clear filters</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $items->appends(request()->query())->links('pagination::tailwind') }}
</div>
@endsection