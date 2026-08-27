@extends('layouts.app')
@section('title', 'Equipment Allocations')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Equipment Allocations</h1>
            <p class="text-slate-400 mt-1">Track equipment assigned to events and waiters</p>
        </div>
        <a href="{{ route('event-equipment.create') }}"
            class="btn-primary text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700">
            <i class="fas fa-plus"></i> New Allocation
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <form action="{{ route('event-equipment.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">

            {{-- Search — compact rounded icon style --}}
            <div class="w-full lg:w-64">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">Search</label>
                <div class="relative group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-0 rounded-2xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition-all">
                    <i
                        class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-amber-500 transition"></i>
                </div>
            </div>

            {{-- Filter by Status --}}
            <div class="w-full lg:w-44">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                <select name="status"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white transition">
                    <option value="">All Status</option>
                    <option value="allocated" {{ request('status') === 'allocated' ? 'selected' : '' }}>Allocated</option>
                    <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>In Use</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="partially_returned" {{ request('status') === 'partially_returned' ? 'selected' : '' }}>
                        Partially Returned</option>
                    <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
            </div>

            {{-- Filter by Waiter --}}
            <div class="w-full lg:w-52">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Waiter</label>
                <select name="waiter_id"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white transition">
                    <option value="">All Waiters</option>
                    @foreach(\App\Models\Waiter::where('status', 'active')->with('user')->get() as $w)
                        <option value="{{ $w->id }}" {{ request('waiter_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-full lg:w-44">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
            </div>

            {{-- Date To --}}
            <div class="w-full lg:w-44">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto shrink-0">
                <button type="submit"
                    class="px-5 py-3 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition flex items-center gap-2">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('event-equipment.index') }}"
                    class="px-5 py-3 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition flex items-center gap-2">
                    <i class="fas fa-undo text-xs"></i> Reset
                </a>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if(request('search') || request('status') || request('waiter_id') || request('from') || request('to'))
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">Active filters:</span>
                @if(request('search'))
                    <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                        Search: "{{ request('search') }}" <a
                            href="{{ route('event-equipment.index', array_diff_key(request()->all(), ['search' => 1])) }}"
                            class="ml-1 hover:text-amber-900"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }} <a
                            href="{{ route('event-equipment.index', array_diff_key(request()->all(), ['status' => 1])) }}"
                            class="ml-1 hover:text-blue-900"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('waiter_id'))
                    @php $wName = \App\Models\Waiter::find(request('waiter_id'))?->user?->name ?? 'Unknown'; @endphp
                    <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                        Waiter: {{ $wName }} <a
                            href="{{ route('event-equipment.index', array_diff_key(request()->all(), ['waiter_id' => 1])) }}"
                            class="ml-1 hover:text-purple-900"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('from'))
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        From: {{ request('from') }} <a
                            href="{{ route('event-equipment.index', array_diff_key(request()->all(), ['from' => 1])) }}"
                            class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('to'))
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        To: {{ request('to') }} <a
                            href="{{ route('event-equipment.index', array_diff_key(request()->all(), ['to' => 1])) }}"
                            class="ml-1 hover:text-emerald-900"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Count --}}
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-500">
            Showing <span
                class="font-semibold text-slate-700">{{ $allocations->firstItem() ?? 0 }}–{{ $allocations->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $allocations->total() }}</span> allocations
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Equipment
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waiter
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allocations as $alloc)
                        <tr class="table-row group hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 group-hover:text-amber-600 transition">
                                    {{ $alloc->event->event_name }}</div>
                                <div class="text-xs text-slate-400">{{ $alloc->event->event_date->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $alloc->equipmentItem->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                        {{ strtoupper(substr($alloc->assignedWaiter->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span
                                        class="text-sm text-slate-600">{{ $alloc->assignedWaiter->user->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ $alloc->quantity_allocated }}</td>
                            <td class="px-6 py-4">
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
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
                                    <a href="{{ route('event-equipment.show', $alloc) }}"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if($alloc->status === 'allocated')
                                        <a href="{{ route('event-equipment.edit', $alloc) }}"
                                            class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                            title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="{{ route('event-equipment.destroy', $alloc) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this allocation?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                                title="Delete">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(!in_array($alloc->status, ['returned', 'lost']))
                                        <button onclick="openReturnModal({{ $alloc->id }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition"
                                            title="Return">
                                            <i class="fas fa-undo text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-hand-holding text-4xl mb-3 text-slate-200"></i>
                                <p class="text-sm">No allocations found matching your criteria.</p>
                                <a href="{{ route('event-equipment.index') }}"
                                    class="text-amber-600 hover:underline text-sm mt-1 inline-block">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $allocations->appends(request()->query())->links('pagination::tailwind') }}
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
                        <input type="number" name="quantity_returned"
                            class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-0 transition"
                            min="0" value="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Damaged</label>
                        <input type="number" name="quantity_damaged"
                            class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-0 transition"
                            min="0" value="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Missing</label>
                        <input type="number" name="quantity_missing"
                            class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-0 transition"
                            min="0" value="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Notes</label>
                        <textarea name="notes"
                            class="w-full border-2 border-slate-100 rounded-xl px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-0 transition"
                            rows="2"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('returnModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition">Save
                        Return</button>
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