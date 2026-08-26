@extends('layouts.app')
@section('title', 'Equipment Allocations')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Equipment Allocations</h1>
        <p class="text-slate-400 mt-1">Track equipment assigned to events and waiters</p>
    </div>
    <a href="{{ route('event-equipment.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start">
        <i class="fas fa-plus"></i> New Allocation
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($allocations as $alloc)
                <tr class="table-row group">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 group-hover:text-orange-600 transition">{{ $alloc->event->event_name }}</div>
                        <div class="text-xs text-slate-400">{{ $alloc->event->event_date->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $alloc->equipmentItem->name }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                {{ strtoupper(substr($alloc->assignedWaiter->user->name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-sm text-slate-600">{{ $alloc->assignedWaiter->user->name ?? 'Unassigned' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ $alloc->quantity_allocated }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge px-3 py-1 rounded-full text-xs
                            {{ $alloc->status === 'returned' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $alloc->status === 'allocated' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $alloc->status === 'in_use' ? 'bg-purple-50 text-purple-600' : '' }}
                            {{ $alloc->status === 'partially_returned' ? 'bg-amber-50 text-amber-600' : '' }}
                            {{ $alloc->status === 'lost' ? 'bg-red-50 text-red-600' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $alloc->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('event-equipment.show', $alloc) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @if($alloc->status === 'allocated')
                            <a href="{{ route('event-equipment.edit', $alloc) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form action="{{ route('event-equipment.destroy', $alloc) }}" method="POST" class="inline" onsubmit="return confirm('Delete this allocation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endif
                            @if(!in_array($alloc->status, ['returned', 'lost']))
                            <button onclick="openReturnModal({{ $alloc->id }})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition" title="Return">
                                <i class="fas fa-undo text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($allocations->isEmpty())
    <div class="px-6 py-12 text-center text-slate-400">
        <i class="fas fa-hand-holding text-4xl mb-3 text-slate-200"></i>
        <p>No allocations found.</p>
    </div>
    @endif
</div>

<div class="mt-6">
    {{ $allocations->links('pagination::tailwind') }}
</div>

<!-- Return Modal -->
<div id="returnModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl animate-fade-in">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Record Equipment Return</h3>
        <form id="returnForm" method="POST">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Returned (Good)</label>
                    <input type="number" name="quantity_returned" class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-0 transition" min="0" value="0" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Damaged</label>
                    <input type="number" name="quantity_damaged" class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-0 transition" min="0" value="0" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Missing</label>
                    <input type="number" name="quantity_missing" class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-0 transition" min="0" value="0" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Notes</label>
                    <textarea name="notes" class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-orange-500 focus:ring-0 transition" rows="2"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition">Save Return</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(id) {
    document.getElementById('returnForm').action = '/event-equipment/' + id + '/return';
    document.getElementById('returnModal').classList.remove('hidden');
}
</script>
@endsection