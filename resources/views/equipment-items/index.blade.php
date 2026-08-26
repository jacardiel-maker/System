@extends('layouts.app')
@section('title', 'Equipment')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Inventory</h1>
        <p class="text-slate-400 mt-1">Track all catering equipment and stock levels</p>
    </div>
    <a href="{{ route('equipment-items.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start">
        <i class="fas fa-plus"></i> Add Item
    </a>
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
                @foreach($items as $item)
                <tr class="table-row group {{ $item->available_quantity < 5 ? 'bg-red-50/30' : '' }}">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 group-hover:text-orange-600 transition">{{ $item->name }}</div>
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
                        <span class="status-badge px-3 py-1 rounded-full text-xs {{ $item->condition === 'new' ? 'bg-emerald-50 text-emerald-600' : ($item->condition === 'good' ? 'bg-blue-50 text-blue-600' : ($item->condition === 'fair' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')) }}">
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
                @endforeach
            </tbody>
        </table>
    </div>
    @if($items->isEmpty())
    <div class="px-6 py-12 text-center text-slate-400">
        <i class="fas fa-box text-4xl mb-3 text-slate-200"></i>
        <p>No equipment items found.</p>
    </div>
    @endif
</div>

<div class="mt-6">
    {{ $items->links('pagination::tailwind') }}
</div>
@endsection