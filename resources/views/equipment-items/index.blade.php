@extends('layouts.app')
@section('title', 'Equipment Inventory')

@section('content')
    @php
        $readOnly = auth()->user()->isOwner(); // owner can monitor stock but not change it
        $categories = \App\Models\EquipmentCategory::orderBy('name')->get();
        $lowStockPercent = 20; // Low Stock = available below 20% of total (TC-INV-A06)

        $conditionStyles = [
            'new'     => 'bg-emerald-50 text-emerald-600',
            'good'    => 'bg-blue-50 text-blue-600',
            'fair'    => 'bg-amber-50 text-amber-600',
            'damaged' => 'bg-red-50 text-red-600',
        ];
        $stockLabels = ['low' => 'Low Stock', 'out' => 'Out of Stock', 'available' => 'Available'];
        // item id => units reported damaged on returns (sent by the controller)
        $damagedUnitsMap = $damagedUnits ?? collect();

        $currentCondition = strtolower((string) request('condition'));
        $currentStock = strtolower((string) request('stock'));
        $hasFilters = request('search') || request('category') || request('condition') || request('stock');

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('equipment-items.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Inventory</h1>
            <p class="text-slate-400 text-sm">{{ $readOnly ? 'Monitor all catering equipment and stock levels' : 'Track all catering equipment and stock levels' }}</p>
        </div>
        @unless($readOnly)
        <a href="{{ route('equipment-items.create') }}"
            class="text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700 transition">
            <i class="fas fa-plus text-xs"></i> Add Item
        </a>
        @endunless
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('equipment-items.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">

            {{-- Search by name or item code --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search item name or item code..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filter by Category --}}
            <div class="w-full lg:w-40">
                <label for="f-category" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                <select id="f-category" name="category" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter by Condition --}}
            <div class="w-full lg:w-32">
                <label for="f-condition" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Condition</label>
                <select id="f-condition" name="condition" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All</option>
                    @foreach(['new' => 'New', 'good' => 'Good', 'fair' => 'Fair', 'damaged' => 'Damaged'] as $value => $label)
                        <option value="{{ $value }}" {{ $currentCondition === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter by Stock Level --}}
            <div class="w-full lg:w-36">
                <label for="f-stock" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Stock</label>
                <select id="f-stock" name="stock" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">All Stock</option>
                    @foreach($stockLabels as $value => $label)
                        <option value="{{ $value }}" {{ $currentStock === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('equipment-items.index') }}" title="Reset" aria-label="Reset"
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
                @if(request('category'))
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Category: {{ $categories->firstWhere('id', request('category'))?->name ?? 'Unknown' }}
                        <a href="{{ $removeFilter('category') }}" class="ml-1 hover:text-blue-900" aria-label="Remove category filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('condition'))
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                        Condition: {{ ucfirst($currentCondition) }}
                        <a href="{{ $removeFilter('condition') }}" class="ml-1 hover:text-emerald-900" aria-label="Remove condition filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('stock'))
                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                        Stock: {{ $stockLabels[$currentStock] ?? ucfirst($currentStock) }}
                        <a href="{{ $removeFilter('stock') }}" class="ml-1 hover:text-purple-900" aria-label="Remove stock filter"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Count + legend --}}
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $items->total() }}</span> items
        </p>
        <div class="flex items-center gap-3 text-[11px] text-slate-400">
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Out / Low (&lt; {{ $lowStockPercent }}% of total)</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Below 50%</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Healthy</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item Code</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Condition</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                        @php
                            $available = (int) $item->available_quantity;
                            $total = (int) $item->total_quantity;
                            $pct = $total > 0 ? max(0, min(100, ($available / $total) * 100)) : 0;
                            $isOut = $available <= 0;
                            $isLow = !$isOut && $total > 0 && ($available * 100) < ($total * $lowStockPercent);

                            $barColor = ($isOut || $isLow || $pct < 20) ? 'bg-red-500' : ($pct < 50 ? 'bg-amber-500' : 'bg-emerald-500');

                            $condition = strtolower(trim((string) $item->condition));
                            // Damaged units reported on returns make the item count as damaged
                            $itemDamagedUnits = (int) ($damagedUnitsMap[$item->id] ?? 0);
                            if ($itemDamagedUnits > 0) {
                                $condition = 'damaged';
                            }
                            $conditionBadge = $conditionStyles[$condition] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <tr class="group {{ ($isOut || $isLow) ? 'bg-red-50/40' : '' }} hover:bg-slate-50/60 transition">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2.5">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                            class="w-8 h-8 shrink-0 rounded-md object-cover bg-slate-100">
                                    @else
                                        <div class="w-8 h-8 shrink-0 rounded-md bg-amber-50 flex items-center justify-center text-amber-500">
                                            <i class="fas fa-box text-xs"></i>
                                        </div>
                                    @endif
                                    <a href="{{ route('equipment-items.show', $item) }}"
                                        class="text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition leading-tight">
                                        {{ $item->name }}</a>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium whitespace-nowrap">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-400 font-mono whitespace-nowrap">{{ $item->serial_code ?: '—' }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-14 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $barColor }}" style="width: {{ round($pct) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold whitespace-nowrap {{ ($isOut || $isLow) ? 'text-red-600' : 'text-slate-600' }}">
                                        {{ $available }}/{{ $total }}
                                    </span>
                                    @if($isOut)
                                        <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-bold uppercase whitespace-nowrap">Out</span>
                                    @elseif($isLow)
                                        <span class="px-1.5 py-0.5 rounded bg-red-50 text-red-600 text-[10px] font-bold uppercase whitespace-nowrap">Low</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $conditionBadge }}">
                                    {{ ucfirst($condition ?: 'unknown') }}
                                </span>
                                @if($itemDamagedUnits > 0)
                                    <div class="text-[11px] text-red-600 mt-0.5 whitespace-nowrap">{{ number_format($itemDamagedUnits) }} damaged {{ \Illuminate\Support\Str::plural('unit', $itemDamagedUnits) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('equipment-items.show', $item) }}"
                                        class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View" aria-label="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @unless($readOnly)
                                    <a href="{{ route('equipment-items.edit', $item) }}"
                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                        title="Edit" aria-label="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('equipment-items.destroy', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="openDeleteDialog({{ $item->id }}, {{ json_encode($item->name) }})"
                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                            title="Delete" aria-label="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-box text-2xl mb-2 text-slate-200 block"></i>
                                <p class="text-xs">No equipment items found matching your criteria.</p>
                                @if($hasFilters)
                                    <a href="{{ route('equipment-items.index') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @elseif(!$readOnly)
                                    <a href="{{ route('equipment-items.create') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Add your first item</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->appends(request()->query())->links('pagination::tailwind') }}
    </div>

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
            <div class="bg-slate-50 rounded-xl p-3 mb-5">
                <div class="text-xs text-slate-500 mb-0.5">Equipment</div>
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
            let pendingDeleteId = null;
            const modal = document.getElementById('deleteModal');

            window.openDeleteDialog = function (id, name) {
                pendingDeleteId = id;
                document.getElementById('deleteItemName').textContent = name;
                modal.classList.remove('hidden');
            };

            window.closeDeleteDialog = function () {
                modal.classList.add('hidden');
                pendingDeleteId = null;
            };

            document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                if (!pendingDeleteId) return;
                this.disabled = true;
                this.textContent = 'Deleting...';
                document.getElementById('delete-form-' + pendingDeleteId).submit();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) window.closeDeleteDialog();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') window.closeDeleteDialog();
            });
        })();
    </script>
@endsection