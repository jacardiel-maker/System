@extends('layouts.app')
@section('title', $equipmentCategory->name)

@section('content')
    @php
        $imageUrl = $equipmentCategory->image ? asset('storage/' . $equipmentCategory->image) : null;
        $lowStockThreshold = 5;
        $items = $equipmentCategory->items;

        $lowCount = $items->filter(fn ($i) => (int) $i->available_quantity < $lowStockThreshold)->count();
        $totalUnits = (int) $items->sum('total_quantity');
        $availableUnits = (int) $items->sum('available_quantity');

        $conditionConfig = [
            'new'     => 'bg-emerald-50 text-emerald-600',
            'good'    => 'bg-blue-50 text-blue-600',
            'fair'    => 'bg-amber-50 text-amber-600',
            'damaged' => 'bg-red-50 text-red-600',
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                <a href="{{ route('equipment-categories.index') }}" class="hover:text-amber-700 transition-colors">Categories</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-medium truncate">{{ $equipmentCategory->name }}</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">{{ $equipmentCategory->name }}</h1>
                @if($equipmentCategory->code)
                    <span class="inline-block px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 font-mono">{{ $equipmentCategory->code }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('equipment-categories.edit', $equipmentCategory) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <form id="delete-category-form" action="{{ route('equipment-categories.destroy', $equipmentCategory) }}" method="POST" class="inline">
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
        {{-- ================= SIDEBAR ================= --}}
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 border-b border-slate-100">
                    @if($imageUrl)
                        <button type="button" onclick="openImageViewer()" id="categoryImageBtn"
                            class="block w-full aspect-square rounded-lg overflow-hidden bg-white shadow-sm focus:outline-none focus:ring-4 focus:ring-amber-300" title="View image">
                            <img src="{{ $imageUrl }}" alt="{{ $equipmentCategory->name }}" class="w-full h-full object-cover"
                                onerror="document.getElementById('categoryImageBtn').classList.add('hidden'); document.getElementById('categoryImageFallback').classList.remove('hidden'); document.getElementById('imageBrokenNote').classList.remove('hidden');">
                        </button>
                    @endif
                    <div id="categoryImageFallback" class="{{ $imageUrl ? 'hidden' : '' }} w-full aspect-square rounded-lg bg-white shadow-sm flex flex-col items-center justify-center text-amber-500">
                        <i class="fas fa-tags text-4xl mb-2"></i>
                        <a href="{{ route('equipment-categories.edit', $equipmentCategory) }}" class="text-xs font-semibold text-amber-700 hover:underline">Add an image</a>
                    </div>
                    <p id="imageBrokenNote" class="hidden mt-2 text-[11px] text-red-600 text-center">
                        Image file not found. Run <code class="font-mono">php artisan storage:link</code>.
                    </p>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Description</p>
                        <p class="text-sm {{ $equipmentCategory->description ? 'text-slate-700' : 'text-slate-400 italic' }} leading-relaxed">
                            {{ $equipmentCategory->description ?: 'No description provided.' }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-slate-50 py-1.5">
                            <p class="text-sm font-bold text-slate-800">{{ $items->count() }}</p>
                            <p class="text-[10px] text-slate-400">Items</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 py-1.5">
                            <p class="text-sm font-bold text-slate-800">{{ number_format($availableUnits) }}/{{ number_format($totalUnits) }}</p>
                            <p class="text-[10px] text-slate-400">Units Avail.</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 py-1.5">
                            <p class="text-sm font-bold {{ $lowCount ? 'text-red-600' : 'text-emerald-600' }}">{{ $lowCount }}</p>
                            <p class="text-[10px] text-slate-400">Low Stock</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= ITEMS ================= --}}
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Items in this Category</h2>
                        <p class="text-xs text-slate-400">{{ $items->count() }} equipment {{ Str::plural('item', $items->count()) }}</p>
                    </div>
                    <a href="{{ route('equipment-items.create', ['category_id' => $equipmentCategory->id]) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition">
                        <i class="fas fa-plus text-[10px]"></i> Add Item
                    </a>
                </div>

                @if($items->count() > 0)
                    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($items as $item)
                            @php
                                $available = (int) $item->available_quantity;
                                $total = (int) $item->total_quantity;
                                $isOut = $available <= 0;
                                $isLow = !$isOut && $available < $lowStockThreshold;
                                $pct = $total > 0 ? max(0, min(100, ($available / $total) * 100)) : 0;
                                $bar = ($isOut || $isLow || $pct < 20) ? 'bg-red-500' : ($pct < 50 ? 'bg-amber-500' : 'bg-emerald-500');
                                $cond = strtolower(trim((string) $item->condition));
                            @endphp
                            <a href="{{ route('equipment-items.show', $item) }}"
                                class="group rounded-lg border {{ ($isOut || $isLow) ? 'border-red-200 bg-red-50/30' : 'border-slate-100' }} p-3 hover:border-amber-200 hover:shadow-sm transition flex gap-3">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-14 h-14 shrink-0 rounded-md object-cover bg-slate-100">
                                @else
                                    <div class="w-14 h-14 shrink-0 rounded-md bg-amber-50 flex items-center justify-center text-amber-500">
                                        <i class="fas fa-box"></i>
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-xs font-semibold text-slate-800 group-hover:text-amber-700 transition truncate">{{ $item->name }}</p>
                                        <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $conditionConfig[$cond] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($cond ?: 'unknown') }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-mono truncate">{{ $item->serial_code ?: 'No serial code' }}</p>

                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full {{ $bar }}" style="width: {{ round($pct) }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-semibold whitespace-nowrap {{ ($isOut || $isLow) ? 'text-red-600' : 'text-slate-600' }}">
                                            {{ $available }}/{{ $total }}
                                        </span>
                                        @if($isOut)
                                            <span class="px-1 py-0.5 rounded bg-red-100 text-red-700 text-[9px] font-bold uppercase">Out</span>
                                        @elseif($isLow)
                                            <span class="px-1 py-0.5 rounded bg-red-50 text-red-600 text-[9px] font-bold uppercase">Low</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-slate-400">
                        <i class="fas fa-box-open text-2xl mb-2 text-slate-200 block"></i>
                        <p class="text-xs">No items in this category yet.</p>
                        <a href="{{ route('equipment-items.create', ['category_id' => $equipmentCategory->id]) }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Add your first item</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Image Viewer --}}
    @if($imageUrl)
        <div id="imageViewer" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onclick="closeImageViewer()">
            <img src="{{ $imageUrl }}" alt="{{ $equipmentCategory->name }}" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain">
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
                <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Category?</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone.</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 mb-3">
                <div class="text-xs text-slate-500 mb-0.5">Category</div>
                <div class="font-semibold text-slate-800">{{ $equipmentCategory->name }}</div>
            </div>
            @if($items->count() > 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3 mb-3 text-xs flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <span>This category still has {{ $items->count() }} equipment {{ Str::plural('item', $items->count()) }}. Move or delete {{ $items->count() === 1 ? 'it' : 'them' }} first before deleting the category.</span>
                </div>
            @endif
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="closeDeleteDialog()"
                    class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium transition">
                    Cancel
                </button>
                <button type="button" id="deleteConfirmBtn" @if($items->count() > 0) disabled @endif
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
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
                document.getElementById('delete-category-form').submit();
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