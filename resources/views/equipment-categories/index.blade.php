@extends('layouts.app')
@section('title', 'Equipment Categories')

@section('content')
    @php
        $sortLabels = ['latest' => 'Latest First', 'name' => 'Name (A-Z)', 'items' => 'Most Items'];
        $currentSort = strtolower((string) request('sort', 'latest'));
        $sortIsCustom = request('sort') && $currentSort !== 'latest';
        $hasFilters = request('search') || $sortIsCustom;

        // When removing a filter chip, also drop "page" so you don't land on an empty page.
        $removeFilter = function ($key) {
            return route('equipment-categories.index', array_diff_key(request()->query(), [$key => 1, 'page' => 1]));
        };
    @endphp

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 leading-tight">Equipment Categories</h1>
            <p class="text-slate-400 text-sm">Organize and manage your inventory categories</p>
        </div>
        <a href="{{ route('equipment-categories.create') }}"
            class="text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 self-start bg-amber-600 hover:bg-amber-700 transition">
            <i class="fas fa-plus text-xs"></i> Add Category
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3 mb-3">
        <form action="{{ route('equipment-categories.index') }}" method="GET" class="flex flex-col lg:flex-row gap-2 lg:items-end">

            {{-- Search --}}
            <div class="flex-1 w-full">
                <label for="f-search" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by name or code..."
                        class="w-full pl-9 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Sort --}}
            <div class="w-full lg:w-40">
                <label for="f-sort" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Sort By</label>
                <select id="f-sort" name="sort" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    @foreach($sortLabels as $value => $label)
                        <option value="{{ $value }}" {{ $currentSort === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" title="Filter" aria-label="Filter"
                    class="flex-1 lg:flex-none lg:w-9 h-8 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition flex items-center justify-center gap-2 text-xs font-semibold">
                    <i class="fas fa-filter text-xs"></i><span class="lg:hidden">Filter</span>
                </button>
                <a href="{{ route('equipment-categories.index') }}" title="Reset" aria-label="Reset"
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
                @if($sortIsCustom)
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                        Sort: {{ $sortLabels[$currentSort] ?? ucfirst($currentSort) }}
                        <a href="{{ $removeFilter('sort') }}" class="ml-1 hover:text-blue-900" aria-label="Remove sort"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Results Count --}}
    <div class="mb-2">
        <p class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700">{{ $categories->total() }}</span> categories
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Items</th>
                        <th class="px-4 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-2 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($categories as $category)
                        @php $itemsCount = (int) ($category->items_count ?? 0); @endphp
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 shrink-0 rounded-md bg-amber-50 flex items-center justify-center text-amber-600">
                                        <i class="fas fa-tag text-xs"></i>
                                    </div>
                                    <a href="{{ route('equipment-categories.show', $category) }}"
                                        class="text-xs font-semibold text-slate-800 group-hover:text-amber-600 transition leading-tight">
                                        {{ $category->name }}</a>
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if($category->code)
                                    <span class="inline-block px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 font-mono">{{ $category->code }}</span>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-box text-slate-300 text-xs"></i> {{ $itemsCount }}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500 max-w-xs">
                                <span class="block truncate" title="{{ $category->description }}">{{ $category->description ?: '—' }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('equipment-categories.show', $category) }}"
                                        class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"
                                        title="View" aria-label="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('equipment-categories.edit', $category) }}"
                                        class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition"
                                        title="Edit" aria-label="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form id="delete-form-{{ $category->id }}" action="{{ route('equipment-categories.destroy', $category) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="openDeleteDialog({{ $category->id }}, {{ json_encode($category->name) }}, {{ $itemsCount }})"
                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                            title="Delete" aria-label="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-tags text-2xl mb-2 text-slate-200 block"></i>
                                <p class="text-xs">No categories found matching your criteria.</p>
                                @if($hasFilters)
                                    <a href="{{ route('equipment-categories.index') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Clear filters</a>
                                @else
                                    <a href="{{ route('equipment-categories.create') }}" class="text-amber-600 hover:underline text-xs inline-block mt-1">Add your first category</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $categories->appends(request()->query())->links('pagination::tailwind') }}
    </div>

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
                <div id="deleteItemName" class="font-semibold text-slate-800"></div>
            </div>
            <div id="deleteWarning" class="hidden bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3 mb-3 text-xs flex items-start gap-2">
                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                <span id="deleteWarningText"></span>
            </div>
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
            let pendingDeleteId = null;
            const modal = document.getElementById('deleteModal');
            const warning = document.getElementById('deleteWarning');

            window.openDeleteDialog = function (id, name, itemsCount) {
                pendingDeleteId = id;
                document.getElementById('deleteItemName').textContent = name;

                if (itemsCount > 0) {
                    document.getElementById('deleteWarningText').textContent =
                        'This category still has ' + itemsCount + ' equipment item' + (itemsCount === 1 ? '' : 's') +
                        '. Those items may be deleted or left without a category.';
                    warning.classList.remove('hidden');
                } else {
                    warning.classList.add('hidden');
                }

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