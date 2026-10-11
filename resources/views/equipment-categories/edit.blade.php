@extends('layouts.app')
@section('title', 'Edit Category')

@section('content')
@php
    $currentImageUrl = $equipmentCategory->image ? asset('storage/' . $equipmentCategory->image) : null;
@endphp
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('equipment-categories.index') }}" class="hover:text-amber-700 transition-colors">Categories</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('equipment-categories.show', $equipmentCategory) }}" class="hover:text-amber-700 transition-colors truncate max-w-[200px]">{{ $equipmentCategory->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Edit Category</h1>
        <p class="mt-1 text-sm text-slate-500">Update category details, code and image</p>
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
    <form action="{{ route('equipment-categories.update', $equipmentCategory) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Category Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Category Details</h2>
            </div>

            <div class="p-6 space-y-5">
                {{-- Image --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="relative shrink-0">
                        <div id="imagePreviewWrap"
                            class="w-28 h-28 rounded-2xl bg-slate-100 border-2 {{ $currentImageUrl ? 'border-transparent' : 'border-dashed' }} border-slate-300 flex items-center justify-center overflow-hidden @error('image') border-rose-400 @enderror">
                            <img id="imagePreview" src="{{ $currentImageUrl ?? '' }}" alt="{{ $equipmentCategory->name }}"
                                class="{{ $currentImageUrl ? '' : 'hidden' }} w-full h-full object-cover">
                            <svg id="imagePlaceholder" class="{{ $currentImageUrl ? 'hidden' : '' }} w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <button type="button" id="imageRemove" aria-label="Remove image" title="Remove image"
                            class="{{ $currentImageUrl ? '' : 'hidden' }} absolute -top-2 -right-2 w-6 h-6 rounded-full bg-white border border-slate-200 shadow-sm text-slate-500 hover:text-rose-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Category Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <div class="flex items-center gap-2">
                            <label for="image"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-amber-300 cursor-pointer transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span id="imageBtnText">{{ $currentImageUrl ? 'Change Image' : 'Upload Image' }}</span>
                            </label>
                            <button type="button" id="imageUndo" class="hidden text-xs font-semibold text-slate-500 hover:text-amber-700 px-2 py-1">Undo</button>
                        </div>
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        <input type="hidden" name="remove_image" id="removeImage" value="0">
                        <p id="imageHint" class="text-xs text-slate-500">JPG, PNG or WEBP. Max 2MB.</p>
                        @error('image')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $equipmentCategory->name) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="e.g., Plates">
                        @error('name')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $equipmentCategory->code) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all uppercase @error('code') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="e.g., PLT">
                        @error('code')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Description <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="description" rows="3"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none @error('description') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="Brief description of this category...">{{ old('description', $equipmentCategory->description) }}</textarea>
                    @error('description')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('equipment-categories.show', $equipmentCategory) }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Category
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
        const undoBtn = document.getElementById('imageUndo');
        const removeFlag = document.getElementById('removeImage');
        const btnText = document.getElementById('imageBtnText');
        const hint = document.getElementById('imageHint');
        const originalUrl = @json($currentImageUrl);
        const MAX_BYTES = 2 * 1024 * 1024;
        const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];
        const DEFAULT_HINT = 'JPG, PNG or WEBP. Max 2MB.';

        function showImage(src) {
            preview.src = src;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            wrap.classList.remove('border-dashed');
            removeBtn.classList.remove('hidden');
            btnText.textContent = 'Change Image';
        }

        function showPlaceholder() {
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            wrap.classList.add('border-dashed');
            removeBtn.classList.add('hidden');
            btnText.textContent = 'Upload Image';
        }

        function setHint(text, isError) {
            hint.textContent = text;
            hint.className = 'text-xs ' + (isError ? 'text-rose-600 font-medium' : 'text-slate-500 truncate max-w-[220px]');
        }

        function restoreOriginal() {
            input.value = '';
            removeFlag.value = '0';
            undoBtn.classList.add('hidden');
            originalUrl ? showImage(originalUrl) : showPlaceholder();
            setHint(DEFAULT_HINT, false);
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            if (!ALLOWED.includes(file.type)) { restoreOriginal(); return setHint('Please choose a JPG, PNG or WEBP image.', true); }
            if (file.size > MAX_BYTES) { restoreOriginal(); return setHint('Image is too large. Max 2MB.', true); }

            const reader = new FileReader();
            reader.onload = e => {
                removeFlag.value = '0';
                showImage(e.target.result);
                setHint(file.name, false);
                undoBtn.classList.toggle('hidden', !originalUrl);
            };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', function () {
            input.value = '';
            showPlaceholder();
            if (originalUrl) {
                removeFlag.value = '1';
                setHint('Image will be removed when you save.', false);
                undoBtn.classList.remove('hidden');
            } else {
                setHint(DEFAULT_HINT, false);
            }
        });

        undoBtn.addEventListener('click', restoreOriginal);
    })();
</script>
@endsection