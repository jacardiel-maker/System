@extends('layouts.app')
@section('title', $equipmentItem->name)

@section('content')
    @php
        $imageUrl = $equipmentItem->image ? asset('storage/' . $equipmentItem->image) : null;
        $lowStockPercent = 20; // Low Stock = available below 20% of total (TC-INV-A06)

        $available = (int) $equipmentItem->available_quantity;
        $total = (int) $equipmentItem->total_quantity;
        $allocated = max(0, $total - $available);
        $pct = $total > 0 ? max(0, min(100, ($available / $total) * 100)) : 0;
        $isOut = $available <= 0;
        $isLow = !$isOut && $total > 0 && ($available * 100) < ($total * $lowStockPercent);
        $barColor = ($isOut || $isLow || $pct < 20) ? 'bg-red-500' : ($pct < 50 ? 'bg-amber-500' : 'bg-emerald-500');

        $conditionConfig = [
            'new'     => ['bg-emerald-100 text-emerald-700', 'New'],
            'good'    => ['bg-blue-100 text-blue-700', 'Good'],
            'fair'    => ['bg-amber-100 text-amber-700', 'Fair'],
            'damaged' => ['bg-red-100 text-red-700', 'Damaged'],
        ];
        $condition = strtolower(trim((string) $equipmentItem->condition));
        $cConfig = $conditionConfig[$condition] ?? ['bg-slate-100 text-slate-600', ucfirst($condition ?: 'unknown')];

        $allocConfig = [
            'allocated'          => ['bg-blue-50 text-blue-600', 'Allocated'],
            'in_use'             => ['bg-purple-50 text-purple-600', 'In Use'],
            'returned'           => ['bg-emerald-50 text-emerald-600', 'Returned'],
            'partially_returned' => ['bg-amber-50 text-amber-600', 'Partially Returned'],
            'lost'               => ['bg-red-50 text-red-600', 'Lost'],
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('equipment-items.index') }}" class="hover:text-amber-700 transition-colors">Equipment</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium truncate">{{ $equipmentItem->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">{{ $equipmentItem->name }}</h1>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('equipment-items.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Back
            </a>
            <a href="{{ route('equipment-items.edit', $equipmentItem) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <form id="delete-item-form" action="{{ route('equipment-items.destroy', $equipmentItem) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button" onclick="openDeleteDialog()"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-200 transition">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Sidebar --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
                {{-- Image --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 border-b border-slate-100">
                    @if($imageUrl)
                        <button type="button" onclick="openImageViewer()" id="itemImageBtn"
                            class="block w-full aspect-square rounded-lg overflow-hidden bg-white shadow-sm focus:outline-none focus:ring-4 focus:ring-amber-300" title="View image">
                            <img src="{{ $imageUrl }}" alt="{{ $equipmentItem->name }}" class="w-full h-full object-cover"
                                onerror="document.getElementById('itemImageBtn').classList.add('hidden'); document.getElementById('itemImageFallback').classList.remove('hidden'); document.getElementById('imageBrokenNote').classList.remove('hidden');">
                        </button>
                    @endif
                    <div id="itemImageFallback" class="{{ $imageUrl ? 'hidden' : '' }} w-full aspect-square rounded-lg bg-white shadow-sm flex flex-col items-center justify-center text-amber-500">
                        <i class="fas fa-box-open text-4xl mb-2"></i>
                        <a href="{{ route('equipment-items.edit', $equipmentItem) }}" class="text-xs font-semibold text-amber-700 hover:underline">Add an image</a>
                    </div>
                    <p id="imageBrokenNote" class="hidden mt-2 text-[11px] text-red-600 text-center">
                        Image file not found. Run <code class="font-mono">php artisan storage:link</code>.
                    </p>

                    <div class="mt-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $cConfig[0] }}">{{ $cConfig[1] }}</span>
                    </div>
                </div>

                {{-- Details --}}
                <div class="p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-tag text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Category</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $equipmentItem->category->name ?? 'Uncategorized' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-barcode text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Serial Code</p>
                            <p class="text-sm font-semibold text-slate-700 font-mono">{{ $equipmentItem->serial_code ?: 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Stock summary --}}
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Stock</p>
                            @if($isOut)
                                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-bold uppercase">Out</span>
                            @elseif($isLow)
                                <span class="px-1.5 py-0.5 rounded bg-red-50 text-red-600 text-[10px] font-bold uppercase">Low</span>
                            @endif
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ round($pct) }}%"></div>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-slate-50 py-1.5">
                                <p class="text-sm font-bold {{ ($isOut || $isLow) ? 'text-red-600' : 'text-emerald-600' }}">{{ $available }}</p>
                                <p class="text-[10px] text-slate-400">Available</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 py-1.5">
                                <p class="text-sm font-bold text-slate-700">{{ $allocated }}</p>
                                <p class="text-[10px] text-slate-400">Allocated</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 py-1.5">
                                <p class="text-sm font-bold text-slate-700">{{ $total }}</p>
                                <p class="text-[10px] text-slate-400">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Allocation History --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Allocation History</h2>
                    <p class="text-xs text-slate-400">{{ $equipmentItem->eventAllocations->count() }} total {{ Str::plural('allocation', $equipmentItem->eventAllocations->count()) }}</p>
                </div>

                @if($equipmentItem->eventAllocations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Event</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waiter</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($equipmentItem->eventAllocations as $alloc)
                                    @php
                                        $aStatus = strtolower(trim((string) $alloc->status));
                                        $aConfig = $allocConfig[$aStatus] ?? ['bg-slate-100 text-slate-600', ucfirst(str_replace('_', ' ', $aStatus))];
                                        $waiterName = $alloc->assignedWaiter->user->name ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-4 py-2">
                                            @if($alloc->event)
                                                <a href="{{ route('events.show', $alloc->event) }}" class="block text-xs font-semibold text-slate-800 hover:text-amber-600 transition leading-tight">{{ $alloc->event->event_name }}</a>
                                                <div class="text-xs text-slate-400 leading-tight">{{ $alloc->event->event_date?->format('M d, Y') }}</div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">Deleted event</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs {{ $waiterName ? 'text-slate-600' : 'text-slate-400 italic' }}">{{ $waiterName ?? 'Unassigned' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">{{ (int) $alloc->quantity_allocated }}</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $aConfig[0] }}">{{ $aConfig[1] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-history text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">This item hasn't been allocated to any events yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Image Viewer --}}
    @if($imageUrl)
        <div id="imageViewer" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onclick="closeImageViewer()">
            <img src="{{ $imageUrl }}" alt="{{ $equipmentItem->name }}" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain">
            <button type="button" onclick="closeImageViewer()" aria-label="Close"
                class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 text-white hover:bg-white/20 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center bg-red-100 text-red-600">
                    <i class="fas fa-trash text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Item?</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-3">
                <div class="text-xs text-slate-500 mb-0.5">Equipment</div>
                <div class="font-semibold text-slate-800">{{ $equipmentItem->name }}</div>
            </div>
            @if($allocated > 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3 mb-3 text-xs flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <span>{{ $allocated }} {{ Str::plural('unit', $allocated) }} of this item {{ $allocated === 1 ? 'is' : 'are' }} currently allocated to events.</span>
                </div>
            @endif
            <div class="flex gap-3 mt-5">
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
            const deleteModal = document.getElementById('deleteModal');
            const viewer = document.getElementById('imageViewer');

            window.openDeleteDialog = () => deleteModal.classList.remove('hidden');
            window.closeDeleteDialog = () => deleteModal.classList.add('hidden');
            window.openImageViewer = () => viewer && viewer.classList.remove('hidden');
            window.closeImageViewer = () => viewer && viewer.classList.add('hidden');

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('delete-item-form').submit();
            });

            deleteModal.addEventListener('click', e => { if (e.target === deleteModal) window.closeDeleteDialog(); });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    window.closeDeleteDialog();
                    window.closeImageViewer();
                }
            });
        })();
    </script>
@endsection