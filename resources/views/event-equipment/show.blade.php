@extends('layouts.app')
@section('title', 'Allocation Details')

@section('content')
@php
    $status = strtolower(trim((string) $eventEquipment->status));
    $statusConfig = [
        'allocated'          => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated', 'fa-box'],
        'in_use'             => ['bg-purple-50 text-purple-700 ring-purple-600/20', 'In Use', 'fa-utensils'],
        'partially_returned' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Partially Returned', 'fa-adjust'],
        'returned'           => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned', 'fa-check-circle'],
        'lost'               => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Lost', 'fa-exclamation-triangle'],
    ];
    $config = $statusConfig[$status]
        ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst(str_replace('_', ' ', $status ?: 'unknown')), 'fa-circle'];

    $itemName  = $eventEquipment->equipmentItem->name ?? 'Deleted item';
    $allocated = (int) $eventEquipment->quantity_allocated;
    $returned  = (int) $eventEquipment->quantity_returned;
    $damaged   = (int) $eventEquipment->quantity_damaged;
    $missing   = (int) $eventEquipment->quantity_missing;
    $isClosed  = in_array($status, ['returned', 'lost'], true);
    $pending   = $isClosed ? 0 : max(0, $allocated - $returned - $damaged - $missing);

    $readOnly  = auth()->user()->isOwner(); // owner: view only, no edit / return / delete

    $canEdit   = !$readOnly && $status === 'allocated';
    $canReturn = !$readOnly && !$isClosed;
    $canDelete = !$readOnly && $status === 'allocated';

    $logConfig = [
        'allocated' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Allocated'],
        'returned'  => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Returned'],
        'damaged'   => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Damaged'],
        'missing'   => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Missing'],
    ];
    $logs = $eventEquipment->logs->sortByDesc('created_at');
@endphp

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                <a href="{{ route('event-equipment.index') }}" class="hover:text-amber-700 transition-colors">Allocations</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-slate-700 font-medium">Details</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight truncate">{{ $itemName }}</h1>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {{ $config[0] }}">
                    <i class="fas {{ $config[2] }} text-[9px]"></i> {{ $config[1] }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Allocation #{{ $eventEquipment->id }}</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('event-equipment.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            @if($canEdit)
                <a href="{{ route('event-equipment.edit', $eventEquipment) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            @endif
            @if($canReturn)
                <button type="button" onclick="openReturnModal()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                    <i class="fas fa-undo text-xs"></i> Record Return
                </button>
            @endif
            @if($canDelete)
                <button type="button" onclick="openDeleteModal()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
            @endif
        </div>
    </div>

    {{-- Validation errors from a failed return --}}
    @if($errors->any())
        <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
            <div class="font-semibold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Please fix the following:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left: details + logs --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Allocation Info --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-box text-xs"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Allocation Info</h2>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 divide-slate-100">
                    <div class="px-4 py-3 sm:border-b sm:border-r border-slate-100">
                        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Event</dt>
                        <dd class="mt-0.5">
                            @if($eventEquipment->event)
                                <a href="{{ route('events.show', $eventEquipment->event) }}"
                                    class="text-sm font-semibold text-amber-700 hover:text-amber-800 hover:underline">
                                    {{ $eventEquipment->event->event_name }}
                                </a>
                                <div class="text-xs text-slate-500">
                                    {{ optional($eventEquipment->event->event_date)->format('M d, Y') ?? '—' }}
                                </div>
                            @else
                                <span class="text-sm text-slate-400 italic">Deleted event</span>
                            @endif
                        </dd>
                    </div>

                    <div class="px-4 py-3 sm:border-b border-slate-100">
                        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Equipment</dt>
                        <dd class="mt-0.5">
                            @if($eventEquipment->equipmentItem)
                                <a href="{{ route('equipment-items.show', $eventEquipment->equipmentItem) }}"
                                    class="text-sm font-semibold text-slate-900 hover:text-amber-700">{{ $itemName }}</a>
                            @else
                                <span class="text-sm text-slate-400 italic">Deleted item</span>
                            @endif
                            <div class="text-xs text-slate-500">{{ $eventEquipment->equipmentItem?->category?->name ?? '—' }}</div>
                        </dd>
                    </div>

                    <div class="px-4 py-3 sm:border-r border-slate-100">
                        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Assigned To</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                            {{ $eventEquipment->assignedWaiter->user->name ?? 'Unassigned' }}
                        </dd>
                    </div>

                    <div class="px-4 py-3">
                        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Allocated On</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                            {{ optional($eventEquipment->created_at)->format('M d, Y') ?? '—' }}
                            <span class="text-xs font-normal text-slate-500">{{ optional($eventEquipment->created_at)->format('h:i A') }}</span>
                        </dd>
                    </div>
                </dl>

                @if($eventEquipment->notes)
                    <div class="px-4 py-3 border-t border-slate-100">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Notes</div>
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $eventEquipment->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Activity Logs --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i class="fas fa-history text-xs"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">Activity Logs</h2>
                    </div>
                    <span class="text-xs text-slate-500">{{ $logs->count() }} {{ \Illuminate\Support\Str::plural('entry', $logs->count()) }}</span>
                </div>

                @if($logs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50/70">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                                    <th class="px-4 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">By</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($logs as $log)
                                    @php
                                        $action = strtolower(trim((string) $log->action));
                                        $lc = $logConfig[$action] ?? ['bg-slate-50 text-slate-600 ring-slate-500/10', ucfirst(str_replace('_', ' ', $action))];
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 ring-inset {{ $lc[0] }}">{{ $lc[1] }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center font-semibold text-slate-800">{{ $log->quantity }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-slate-700">{{ $log->logger->name ?? 'System' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-slate-600">
                                            {{ optional($log->created_at)->format('M d, Y') }}
                                            <span class="text-xs text-slate-400 ml-1">{{ optional($log->created_at)->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-slate-600">{{ $log->remarks ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-history text-slate-300"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">No activity logs</p>
                        <p class="text-xs text-slate-500">No actions have been recorded for this allocation.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: quantity summary --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-xs"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Quantity Summary</h2>
                </div>

                <div class="p-4">
                    <div class="text-center mb-3">
                        <div class="text-3xl font-bold text-slate-900">{{ $allocated }}</div>
                        <div class="text-xs text-slate-500">Allocated</div>
                    </div>

                    @php $accounted = $returned + $damaged + $missing; @endphp
                    @if($allocated > 0)
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden flex mb-3">
                            <div class="bg-emerald-500" style="width: {{ $returned / $allocated * 100 }}%"></div>
                            <div class="bg-amber-500" style="width: {{ $damaged / $allocated * 100 }}%"></div>
                            <div class="bg-rose-500" style="width: {{ $missing / $allocated * 100 }}%"></div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-emerald-50 py-2 text-center">
                            <div class="text-lg font-bold text-emerald-700">{{ $returned }}</div>
                            <div class="text-[11px] font-medium text-emerald-600">Returned</div>
                        </div>
                        <div class="rounded-lg bg-amber-50 py-2 text-center">
                            <div class="text-lg font-bold text-amber-700">{{ $damaged }}</div>
                            <div class="text-[11px] font-medium text-amber-600">Damaged</div>
                        </div>
                        <div class="rounded-lg bg-rose-50 py-2 text-center">
                            <div class="text-lg font-bold text-rose-700">{{ $missing }}</div>
                            <div class="text-[11px] font-medium text-rose-600">Missing</div>
                        </div>
                        <div class="rounded-lg {{ $pending > 0 ? 'bg-blue-50' : 'bg-slate-50' }} py-2 text-center">
                            <div class="text-lg font-bold {{ $pending > 0 ? 'text-blue-700' : 'text-slate-500' }}">{{ $pending }}</div>
                            <div class="text-[11px] font-medium {{ $pending > 0 ? 'text-blue-600' : 'text-slate-500' }}">Pending</div>
                        </div>
                    </div>

                    @if(!$isClosed && $pending > 0)
                        <p class="mt-3 text-xs text-slate-500 text-center">
                            {{ $pending }} {{ \Illuminate\Support\Str::plural('unit', $pending) }} still to be returned.
                        </p>
                    @elseif($isClosed)
                        <p class="mt-3 text-xs text-slate-500 text-center">This allocation is closed.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
@if($canDelete)
    <form id="deleteForm" action="{{ route('event-equipment.destroy', $eventEquipment) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Allocation?</h3>
                <p class="text-slate-500 text-sm">The {{ $allocated }} allocated {{ \Illuminate\Support\Str::plural('unit', $allocated) }} will go back to inventory. This cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Allocation</div>
                <div class="font-semibold text-slate-800">
                    {{ $itemName }} × {{ $allocated }}
                    @if($eventEquipment->event)
                        <span class="text-slate-500 font-normal">— {{ $eventEquipment->event->event_name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
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
@endif

{{-- Return Modal --}}
@if($canReturn)
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
                    <div class="font-semibold text-slate-800 truncate">{{ $itemName }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs text-slate-500 mb-0.5">Allocated</div>
                    <div class="font-bold text-slate-800">{{ $allocated }}</div>
                </div>
            </div>

            <form id="returnForm" action="{{ route('event-equipment.return', $eventEquipment) }}" method="POST">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label for="r-good" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Good</label>
                        <input id="r-good" type="number" name="quantity_returned" min="0" max="{{ $allocated }}"
                            value="{{ old('quantity_returned', $allocated) }}" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-damaged" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Damaged</label>
                        <input id="r-damaged" type="number" name="quantity_damaged" min="0" max="{{ $allocated }}"
                            value="{{ old('quantity_damaged', 0) }}" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="r-missing" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Missing</label>
                        <input id="r-missing" type="number" name="quantity_missing" min="0" max="{{ $allocated }}"
                            value="{{ old('quantity_missing', 0) }}" required
                            class="return-qty w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                </div>

                <div id="returnTotal" class="mt-2 text-xs text-slate-500"></div>

                <div class="mt-3">
                    <label for="r-notes" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Notes</label>
                    <textarea id="r-notes" name="notes" rows="2" placeholder="Optional — condition details, issues found..."
                        class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">{{ old('notes') }}</textarea>
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
@endif

<script>
    (function () {
        // ---------- Return modal ----------
        const returnModal = document.getElementById('returnModal');
        if (returnModal) {
            const allocatedQty = {{ $allocated }};
            const qtyInputs = Array.from(document.querySelectorAll('.return-qty'));
            const totalEl = document.getElementById('returnTotal');
            const submitBtn = document.getElementById('returnSubmitBtn');

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

            window.openReturnModal = function () {
                submitBtn.textContent = 'Save Return';
                updateReturnTotal();
                returnModal.classList.remove('hidden');
            };
            window.closeReturnModal = function () {
                returnModal.classList.add('hidden');
            };

            qtyInputs.forEach(el => el.addEventListener('input', updateReturnTotal));
            document.getElementById('returnForm').addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            });
            returnModal.addEventListener('click', e => { if (e.target === returnModal) window.closeReturnModal(); });

            // Reopen the modal if the server rejected the last return
            @if($errors->has('quantity_returned') || $errors->has('quantity_damaged') || $errors->has('quantity_missing') || $errors->has('notes'))
                window.openReturnModal();
            @endif
        }

        // ---------- Delete modal ----------
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            window.openDeleteModal = function () {
                deleteModal.classList.remove('hidden');
            };
            window.closeDeleteModal = function () {
                deleteModal.classList.add('hidden');
            };
            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('deleteForm').submit();
            });
            deleteModal.addEventListener('click', e => { if (e.target === deleteModal) window.closeDeleteModal(); });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (window.closeReturnModal) window.closeReturnModal();
            if (window.closeDeleteModal) window.closeDeleteModal();
        });
    })();
</script>
@endsection