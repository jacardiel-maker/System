@extends('layouts.app')
@section('title', 'Edit Waiter')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('waiters.index') }}" class="hover:text-amber-700 transition-colors">Waiters</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('waiters.show', $waiter) }}" class="hover:text-amber-700 transition-colors truncate max-w-[200px]">{{ $waiter->user->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Edit</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Edit Waiter</h1>
        <p class="mt-1 text-sm text-slate-500">Update profile, address, and account settings</p>
    </div>

    {{-- Generated Password Alert --}}
    @if(session('generated_password'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 overflow-hidden" id="passwordAlert">
            <div class="px-4 py-3 border-b border-emerald-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <h3 class="text-sm font-bold text-emerald-800">New Password Generated</h3>
            </div>
            <div class="p-4">
                <div class="relative">
                    <div class="rounded-xl bg-white border border-emerald-200 px-4 py-3 text-center font-mono text-lg tracking-widest text-slate-900 select-all" id="generatedPassword">
                        {{ session('generated_password') }}
                    </div>
                    <button type="button" onclick="copyPassword()" class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy
                    </button>
                </div>
                <p class="mt-3 text-xs text-emerald-700 flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Copy this now — it will not be shown again. Share it with {{ $waiter->user->name }} securely.
                </p>
            </div>
        </div>
    @endif

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

    @php
        $currentPhotoUrl = $waiter->photo ? asset('storage/' . $waiter->photo) : null;
    @endphp

    {{-- enctype is required for the photo upload --}}
    <form action="{{ route('waiters.update', $waiter) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Personal Information --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Personal Information</h2>
            </div>

            <div class="p-6 space-y-5">
                {{-- Photo --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="relative shrink-0">
                        <div id="photoPreviewWrap"
                            class="w-24 h-24 rounded-2xl bg-slate-100 border-2 {{ $currentPhotoUrl ? 'border-transparent' : 'border-dashed' }} border-slate-300 flex items-center justify-center overflow-hidden @error('photo') border-rose-400 @enderror">
                            <img id="photoPreview" src="{{ $currentPhotoUrl ?? '' }}" alt="{{ $waiter->user->name }}"
                                class="{{ $currentPhotoUrl ? '' : 'hidden' }} w-full h-full object-cover">
                            <span id="photoPlaceholder" class="{{ $currentPhotoUrl ? 'hidden' : '' }} text-3xl font-bold text-slate-400">
                                {{ strtoupper(mb_substr($waiter->user->name, 0, 1)) }}
                            </span>
                        </div>
                        <button type="button" id="photoRemove" aria-label="Remove photo" title="Remove photo"
                            class="{{ $currentPhotoUrl ? '' : 'hidden' }} absolute -top-2 -right-2 w-6 h-6 rounded-full bg-white border border-slate-200 shadow-sm text-slate-500 hover:text-rose-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Photo <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <div class="flex items-center gap-2">
                            <label for="photo"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-amber-300 cursor-pointer transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span id="photoBtnText">{{ $currentPhotoUrl ? 'Change Photo' : 'Upload Photo' }}</span>
                            </label>
                            <button type="button" id="photoUndo"
                                class="hidden text-xs font-semibold text-slate-500 hover:text-amber-700 px-2 py-1">
                                Undo
                            </button>
                        </div>
                        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        <input type="hidden" name="remove_photo" id="removePhoto" value="0">
                        <p id="photoHint" class="text-xs text-slate-500">JPG, PNG or WEBP. Max 2MB.</p>
                        @error('photo')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $waiter->user->name) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('name')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $waiter->user->email) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('email')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Phone <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $waiter->phone) }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('phone') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('phone')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Emergency Contact</label>
                        <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $waiter->emergency_contact) }}"
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('emergency_contact') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="0918 987 6543">
                        @error('emergency_contact')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Address</h2>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Region <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="region_id" id="region" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('region_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Region</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_id }}" {{ (string) old('region_id', $waiter->region_id) === (string) $region->region_id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('region_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Province <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="province_id" id="province" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('province_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Province</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->province_id }}" {{ (string) old('province_id', $waiter->province_id) === (string) $prov->province_id ? 'selected' : '' }}>
                                        {{ $prov->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('province_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">City / Municipality <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="city_id" id="city" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('city_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select City</option>
                                @foreach($cities as $ct)
                                    <option value="{{ $ct->city_id }}" {{ (string) old('city_id', $waiter->city_id) === (string) $ct->city_id ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('city_id')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Barangay <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select name="barangay_code" id="barangay" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('barangay_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Barangay</option>
                                @foreach($barangays as $brgy)
                                    <option value="{{ $brgy->code }}" {{ (string) old('barangay_code', $waiter->barangay_code) === (string) $brgy->code ? 'selected' : '' }}>
                                        {{ $brgy->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('barangay_code')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Street / Building <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="address" rows="2"
                        class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all resize-none @error('address') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                        placeholder="House number, street name, building, subdivision...">{{ old('address', $waiter->address) }}</textarea>
                    @error('address')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Employment & Account --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-base font-bold text-slate-900">Employment & Account</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Date Hired <span class="text-rose-500">*</span></label>
                        <input type="date" name="date_hired" value="{{ old('date_hired', $waiter->date_hired?->format('Y-m-d')) }}" required
                            max="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('date_hired') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('date_hired')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Status <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            @php $currentStatus = strtolower((string) old('status', $waiter->status)); @endphp
                            <select name="status"
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer @error('status') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('status')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Password Section --}}
                <div class="pt-6 border-t border-slate-100">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" id="changePasswordCheck" name="change_password" value="1"
                            class="w-5 h-5 rounded-lg border-slate-300 text-amber-600 focus:ring-amber-500/20 focus:ring-2 cursor-pointer transition-all"
                            {{ old('change_password') ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Set Custom Password</span>
                    </label>

                    <div id="passwordFields" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5 {{ old('change_password') ? '' : 'hidden' }}">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="adminNewPassword" minlength="8" autocomplete="new-password"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                                    placeholder="••••••••">
                                <button type="button" onclick="toggleEye('adminNewPassword', 'adminEye1')" aria-label="Show password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                    <svg class="w-4 h-4" id="adminEye1-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-slate-400">Minimum 8 characters</p>
                            @error('password')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="adminConfirmPassword" autocomplete="new-password"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all"
                                    placeholder="••••••••">
                                <button type="button" onclick="toggleEye('adminConfirmPassword', 'adminEye2')" aria-label="Show password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                    <svg class="w-4 h-4" id="adminEye2-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="generateRandomPassword()"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-amber-700 hover:text-amber-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Generate random password
                    </button>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('waiters.show', $waiter) }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Waiter
            </button>
        </div>
    </form>
</div>

<script>
    // ---------- Photo: upload / change / remove / undo ----------
    (function () {
        const input = document.getElementById('photo');
        const preview = document.getElementById('photoPreview');
        const placeholder = document.getElementById('photoPlaceholder');
        const wrap = document.getElementById('photoPreviewWrap');
        const removeBtn = document.getElementById('photoRemove');
        const undoBtn = document.getElementById('photoUndo');
        const removeFlag = document.getElementById('removePhoto');
        const btnText = document.getElementById('photoBtnText');
        const hint = document.getElementById('photoHint');
        const originalUrl = @json($currentPhotoUrl);
        const MAX_BYTES = 2 * 1024 * 1024;
        const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];
        const DEFAULT_HINT = 'JPG, PNG or WEBP. Max 2MB.';

        function showImage(src) {
            preview.src = src;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            wrap.classList.remove('border-dashed');
            removeBtn.classList.remove('hidden');
            btnText.textContent = 'Change Photo';
        }

        function showPlaceholder() {
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            wrap.classList.add('border-dashed');
            removeBtn.classList.add('hidden');
            btnText.textContent = 'Upload Photo';
        }

        function setHint(text, isError) {
            hint.textContent = text;
            hint.className = 'text-xs ' + (isError ? 'text-rose-600 font-medium' : 'text-slate-500 truncate max-w-[220px]');
        }

        // Back to how the page loaded (current saved photo, or none)
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
                setHint('Photo will be removed when you save.', false);
                undoBtn.classList.remove('hidden');
            } else {
                setHint(DEFAULT_HINT, false);
            }
        });

        undoBtn.addEventListener('click', restoreOriginal);
    })();

    // ---------- Address cascading dropdowns ----------
    document.addEventListener('DOMContentLoaded', function () {
        const apiBase = @json(url('api'));
        const region = document.getElementById('region');
        const province = document.getElementById('province');
        const city = document.getElementById('city');
        const barangay = document.getElementById('barangay');

        function clearOptions(select, placeholder) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
        }

        function fill(select, data) {
            data.forEach(item => {
                const o = document.createElement('option');
                o.value = item.id;
                o.textContent = item.name;
                select.appendChild(o);
            });
        }

        region.addEventListener('change', function () {
            clearOptions(province, 'Select Province');
            clearOptions(city, 'Select City'); city.disabled = true;
            clearOptions(barangay, 'Select Barangay'); barangay.disabled = true;
            if (!this.value) { province.disabled = true; return; }
            province.disabled = false;
            fetch(`${apiBase}/provinces/${this.value}`).then(r => r.json()).then(data => fill(province, data));
        });

        province.addEventListener('change', function () {
            clearOptions(city, 'Select City');
            clearOptions(barangay, 'Select Barangay'); barangay.disabled = true;
            if (!this.value) { city.disabled = true; return; }
            city.disabled = false;
            fetch(`${apiBase}/cities/${this.value}`).then(r => r.json()).then(data => fill(city, data));
        });

        city.addEventListener('change', function () {
            clearOptions(barangay, 'Select Barangay');
            if (!this.value) { barangay.disabled = true; return; }
            barangay.disabled = false;
            fetch(`${apiBase}/barangays/${this.value}`).then(r => r.json()).then(data => fill(barangay, data));
        });
    });

    // ---------- Password ----------
    document.getElementById('changePasswordCheck').addEventListener('change', function () {
        document.getElementById('passwordFields').classList.toggle('hidden', !this.checked);
        if (!this.checked) {
            // Clear so a hidden half-typed password can't block the save
            document.getElementById('adminNewPassword').value = '';
            document.getElementById('adminConfirmPassword').value = '';
        }
    });

    function generateRandomPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
        const values = new Uint32Array(12);
        crypto.getRandomValues(values);
        const password = Array.from(values, v => chars[v % chars.length]).join('');

        document.getElementById('changePasswordCheck').checked = true;
        document.getElementById('passwordFields').classList.remove('hidden');
        const newPass = document.getElementById('adminNewPassword');
        const confirmPass = document.getElementById('adminConfirmPassword');
        newPass.value = password;
        confirmPass.value = password;
        // Show it so the admin can copy it before saving
        newPass.type = 'text';
        confirmPass.type = 'text';
    }

    function toggleEye(inputId, eyeId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(eyeId + '-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }

    function copyPassword() {
        const text = document.getElementById('generatedPassword').textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('#passwordAlert button');
            const original = btn.innerHTML;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied';
            setTimeout(() => btn.innerHTML = original, 2000);
        });
    }
</script>
@endsection