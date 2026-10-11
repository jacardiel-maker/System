@extends('layouts.app')
@section('title', 'Add Equipment Item')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('equipment-items.index') }}" class="hover:text-amber-700 transition-colors">Equipment</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Add New</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add Equipment Item</h1>
        <p class="mt-1 text-sm text-slate-500">Register a new item to your inventory</p>
    </div>

    {{-- Error Summary --}}
    @if($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 px-4 py-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-rose-800">Please fix the following errors:</h3>
                    <ul class="mt-1.5 text-sm text-rose-700 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- enctype is required for the image upload --}}
    <form action="{{ route('equipment-items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Item Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Item Details</h2>
            </div>

            <div class="p-6 space-y-5">
                {{-- Image --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="relative shrink-0">
                        <div id="imagePreviewWrap"
                            class="w-28 h-28 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden @error('image') border-rose-400 @enderror">
                            <img id="imagePreview" src="" alt="Item image preview" class="hidden w-full h-full object-cover">
                            <svg id="imagePlaceholder" class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <button type="button" id="imageRemove" aria-label="Remove image"
                            class="hidden absolute -top-2 -right-2 w-6 h-6 rounded-full bg-white border border-slate-200 shadow-sm text-slate-500 hover:text-rose-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Item Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <label for="image"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-amber-300 cursor-pointer transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span id="imageBtnText">Upload Image</span>
                        </label>
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        <p id="imageHint" class="text-xs text-slate-500">JPG, PNG or WEBP. Max 2MB.</p>
                        @error('image')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Category <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="category_id" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('category_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            <option value="">Select Category</option>
                            @foreach(\App\Models\EquipmentCategory::orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}" {{ (string) old('category_id', request('category_id')) === (string) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('category_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Item Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="e.g., Round Dinner Plate 10in">
                    @error('name')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Serial Code <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="text" name="serial_code" value="{{ old('serial_code') }}"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('serial_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="e.g., SN-2024-001">
                    @error('serial_code')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Total Quantity <span class="text-rose-500">*</span></label>
                        <input type="number" name="total_quantity" value="{{ old('total_quantity') }}" min="0" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('total_quantity') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="0">
                        @error('total_quantity')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Condition</label>
                        <div class="relative">
                            <select name="condition"
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('condition') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                @foreach(['new' => 'New', 'good' => 'Good', 'fair' => 'Fair', 'damaged' => 'Damaged'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('condition', 'good') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('condition')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('equipment-items.index') }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Item
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
        const input = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const wrap = document.getElementById('imagePreviewWrap');
        const removeBtn = document.getElementById('imageRemove');
        const btnText = document.getElementById('imageBtnText');
        const hint = document.getElementById('imageHint');
        const MAX_BYTES = 2 * 1024 * 1024;
        const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

        function reset(message) {
            input.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            wrap.classList.add('border-dashed');
            removeBtn.classList.add('hidden');
            btnText.textContent = 'Upload Image';
            hint.textContent = message || 'JPG, PNG or WEBP. Max 2MB.';
            hint.className = 'text-xs ' + (message ? 'text-rose-600 font-medium' : 'text-slate-500');
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return reset();
            if (!ALLOWED.includes(file.type)) return reset('Please choose a JPG, PNG or WEBP image.');
            if (file.size > MAX_BYTES) return reset('Image is too large. Max 2MB.');

            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                wrap.classList.remove('border-dashed');
                removeBtn.classList.remove('hidden');
                btnText.textContent = 'Change Image';
                hint.textContent = file.name;
                hint.className = 'text-xs text-slate-500 truncate max-w-[220px]';
            };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', () => reset());
    })();
</script>
@endsection