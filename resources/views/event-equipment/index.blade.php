@extends('layouts.app')
@section('title', 'Equipment Allocations')

@section('content')
    @php
        $readOnly = auth()->user()->isOwner(); // owner can view allocations but not change them
        $waiters = \App\Models\Waiter::where('status', 'active')->with('user')->get()
            ->sortBy(fn ($w) => $w->user->name ?? '')->values();

        $statusLabels = [
            'allocated'          => 'Allocated',
            'in_use'             => 'In Use',
            'returned'           => 'Returned',
            'partially_returned' => 'Partially Returned',
            'lost'               => 'Lost',
        ];
        $statusStyles = [
            'allocated'          => 'bg-blue-50 text-blue-600',
            'in_use'             => 'bg-purple-50 text-purple-600',
            'returned'           => 'bg-emerald-50 text-emerald-600',
            'partially_returned' => 'bg-amber-50 text-amber-600',
            'lost'               => 'bg-red-50 text-red-600',
        ];

        $currentStatus = strtolower((string) request('status'));
        $hasFilters = request('search') || request('status') || request('waiter_id') || request('from') || request('to');

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('event-equipment.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Allocations</h1>
            <p class="text-slate-400 text-sm">{{ $readOnly ? 'Monitor equipment assigned to events and waiters' : 'Track equipment assigned to events and waiters' }}</p>
        </div>
        @unless($readOnly)
        <a href="{{ route('event-equipment.create') }}"
            class="text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700 transition">
            <i class="fas fa-plus text-xs"></i> New Allocation
        </a>
        @endunless
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('event-equipment.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">

            {{-- Search --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search event or equipment..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filter by Status --}}
            <div class="w-full lg:w-40">
                <label for="f-status" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select id="f-status" name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Status</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter by Waiter --}}
            <div class="w-full lg:w-40">
                <label for="f-waiter" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Waiter</label>
                <select id="f-waiter" name="waiter_id" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Waiters</option>
                    @foreach($waiters as $w)
                        <option value="{{ $w->id }}" {{ (string) request('waiter_id') === (string) $w->id ? 'selected' : '' }}>
                            {{ $w->user->name ?? 'Unknown' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-full lg:w-36">
                <label for="f-from" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">From</label>
                <input id="f-from" type="date" name="from" value="{{ request('from') }}" max="{{ request('to') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            {{-- Date To --}}
            <div class="w-full lg:w-36">
                <label for="f-to" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">To</label>
                <input id="f-to" type="date" name="to" value="{{ request('to') }}" min="{{ request('from') }}"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('event-equipment.index') }}" title="Reset" aria-label="Reset"
                    class="flex-1 lg:flex-none lg:w-9 h-8 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-undo text-xs"></i><span class="lg:hidden">Reset</span>
                </a>
            </div>
        </form>

        {{-- Active Filters Display --}}
        @if($hasFilters)
            <div class="mt-2 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <span class="text-xs text-slate-400 font-medium">Active filters:</span>
                @if(request('search'))
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                        Search: "{{ request('search') }}"
                        <a href="{{ $removeFilter('search') }}" class="ml-1 hover:text-amber-900" aria-label="Remove search filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Status: {{ $statusLabels[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus)) }}
                        <a href="{{ $removeFilter('status') }}" class="ml-1 hover:text-blue-900" aria-label="Remove status filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('waiter_id'))
                    @php
                        $wName = $waiters->firstWhere('id', (int) request('waiter_id'))?->user?->name
                            ?? \App\Models\Waiter::with('user')->find(request('waiter_id'))?->user?->name
                            ?? 'Unknown';
                    @endphp
                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                        Waiter: {{ $wName }}
                        <a href="{{ $removeFilter('waiter_id') }}" class="ml-1 hover:text-purple-900" aria-label="Remove waiter filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('from'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        From: {{ \Carbon\Carbon::parse(request('from'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('from') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove from-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('to'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        To: {{ \Carbon\Carbon::parse(request('to'))->format('M d, Y') }}
                        <a href="{{ $removeFilter('to') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove to-date filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Count --}}
    <div class="mb-2">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ $allocations->firstItem() ?? 0 }}–{{ $allocations->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $allocations->total() }}</span> allocations
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allocations as $alloc)
                        @php
                            $event = $alloc->event;
                            $equipment = $alloc->equipmentItem;
                            $waiterName = $alloc->assignedWaiter->user->name ?? null;
                            $status = strtolower(trim((string) $alloc->status));
                            $badge = $statusStyles[$status] ?? 'bg-slate-100 text-slate-600';
                            $statusText = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status ?: 'unknown'));
                            $qty = (int) $alloc->quantity_allocated;
                            $itemName = $equipment->name ?? 'Deleted item';
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2">
                                @if($event)
                                    <a href="{{ route('events.show', $event) }}"
                                        class="block text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition leading-tight">
                                        {{ $event->event_name }}</a>
                                    <div class="text-xs text-slate-400 leading-tight">{{ $event->event_date?->format('M d, Y') }}</div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Deleted event</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if($equipment)
                                    <a href="{{ route('equipment-items.show', $equipment) }}"
                                        class="text-xs text-slate-600 hover:text-amber-600 transition leading-tight">{{ $equipment->name }}</a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Deleted item</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 shrink-0 rounded-full {{ $waiterName ? 'bg-gradient-to-br from-slate-600 to-slate-700 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-[10px] font-bold">
                                        {{ $waiterName ? strtoupper(mb_substr($waiterName, 0, 1)) : '?' }}
                                    </div>
                                    <span class="text-xs {{ $waiterName ? 'text-slate-600' : 'text-slate-400 italic' }} truncate">
                                        {{ $waiterName ?? 'Unassigned' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">
                                    {{ $qty }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $badge }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('event-equipment.show', $alloc) }}"
                                        class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View" aria-label="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @unless($readOnly)
                                    @if(!in_array($status, ['returned', 'lost']))
                                        <button type="button"
                                            onclick="openReturnModal({{ $alloc->id }}, {{ json_encode($itemName) }}, {{ $qty }})"
                                            class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition"
                                            title="Record Return" aria-label="Record Return">
                                            <i class="fas fa-undo text-xs"></i>
                                        </button>
                                    @endif
                                    @if($status === 'allocated')
                                        <a href="{{ route('event-equipment.edit', $alloc) }}"
                                            class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                            title="Edit" aria-label="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form id="delete-form-{{ $alloc->id }}" action="{{ route('event-equipment.destroy', $alloc) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="openDeleteDialog({{ $alloc->id }}, {{ json_encode($itemName . ' × ' . $qty) }})"
                                                class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                                title="Delete" aria-label="Delete">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-hand-holding text-2xl mb-2 text-slate-200 block"></i>
                                <p class="text-xs">No allocations found matching your criteria.</p>
                                @if($hasFilters)
                                    <a href="{{ route('event-equipment.index') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @elseif(!$readOnly)
                                    <a href="{{ route('event-equipment.create') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Create your first allocation</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $allocations->appends(request()->query())->links('pagination::tailwind') }}
    </div>

    {{-- Return Modal --}}
    <div id="returnModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-emerald-100 text-emerald-600">
                    <i class="fas fa-undo text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Record Equipment Return</h3>
                <p class="text-slate-500 text-sm">Enter how many came back and in what condition.</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-3 mb-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs text-slate-500 mb-0.5">Equipment</div>
                    <div id="returnItemName" class="font-semibold text-slate-800 truncate"></div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs text-slate-500 mb-0.5">Allocated</div>
                    <div id="returnAllocated" class="font-bold text-slate-800"></div>
                </div>
            </div>

            <form id="returnForm" method="POST">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label for="r-good" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Good</label>
                        <input id="r-good" type="number" name="quantity_returned" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-damaged" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Damaged</label>
                        <input id="r-damaged" type="number" name="quantity_damaged" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-missing" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Missing</label>
                        <input id="r-missing" type="number" name="quantity_missing" min="0" value="0" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                </div>

                <div id="returnTotal" class="mt-2 text-xs text-slate-500"></div>

                <div class="mt-3">
                    <label for="r-notes" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Notes</label>
                    <textarea id="r-notes" name="notes" rows="2" placeholder="Optional"
                        class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="closeReturnModal()"
                        class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" id="returnSubmitBtn"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Save Return
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Allocation?</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Allocation</div>
                <div id="deleteItemName" class="font-semibold text-slate-800"></div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteDialog()"
                    class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                    Cancel
                </button>
                <button type="button" id="deleteConfirmBtn"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const baseUrl = @json(url('event-equipment'));

            // ---------- Return modal ----------
            const returnModal = document.getElementById('returnModal');
            const returnForm = document.getElementById('returnForm');
            const qtyInputs = Array.from(document.querySelectorAll('.return-qty'));
            const totalEl = document.getElementById('returnTotal');
            const submitBtn = document.getElementById('returnSubmitBtn');
            let allocatedQty = 0;

            function updateReturnTotal() {
                const total = qtyInputs.reduce((sum, el) => sum + (parseInt(el.value, 10) || 0), 0);
                const over = total > allocatedQty;
                const none = total === 0;

                totalEl.textContent = over
                    ? `Total ${total} is more than the ${allocatedQty} allocated.`
                    : `Accounted for: ${total} of ${allocatedQty}` + (total < allocatedQty && !none ? ' (partial return)' : '');
                totalEl.className = 'mt-2 text-xs ' + (over ? 'text-red-600 font-semibold' : 'text-slate-500');
                submitBtn.disabled = over || none;
            }

            window.openReturnModal = function (id, itemName, qty) {
                allocatedQty = parseInt(qty, 10) || 0;
                returnForm.action = baseUrl + '/' + id + '/return';
                returnForm.reset();
                document.getElementById('returnItemName').textContent = itemName;
                document.getElementById('returnAllocated').textContent = allocatedQty;
                qtyInputs.forEach(el => el.max = allocatedQty);
                document.getElementById('r-good').value = allocatedQty; // most returns are complete
                submitBtn.textContent = 'Save Return';
                updateReturnTotal();
                returnModal.classList.remove('hidden');
            };

            window.closeReturnModal = function () {
                returnModal.classList.add('hidden');
            };

            qtyInputs.forEach(el => el.addEventListener('input', updateReturnTotal));

            returnForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            });

            returnModal.addEventListener('click', function (e) {
                if (e.target === returnModal) window.closeReturnModal();
            });

            // ---------- Delete modal ----------
            let pendingDeleteId = null;
            const deleteModal = document.getElementById('deleteModal');

            window.openDeleteDialog = function (id, name) {
                pendingDeleteId = id;
                document.getElementById('deleteItemName').textContent = name;
                deleteModal.classList.remove('hidden');
            };

            window.closeDeleteDialog = function () {
                deleteModal.classList.add('hidden');
                pendingDeleteId = null;
            };

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                if (!pendingDeleteId) return;
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('delete-form-' + pendingDeleteId).submit();
            });

            deleteModal.addEventListener('click', function (e) {
                if (e.target === deleteModal) window.closeDeleteDialog();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.closeReturnModal();
                    window.closeDeleteDialog();
                }
            });

            // ---------- Keep From <= To ----------
            const from = document.getElementById('f-from');
            const to = document.getElementById('f-to');
            from.addEventListener('change', () => { to.min = from.value; });
            to.addEventListener('change', () => { from.max = to.value; });
        })();
    </script>
@endsection