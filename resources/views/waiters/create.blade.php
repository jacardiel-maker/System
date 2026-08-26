@extends('layouts.app')
@section('title', 'Add Waiter')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="{{ route('waiters.index') }}" class="hover:text-amber-700 transition-colors">Waiters</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Add New</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add New Waiter</h1>
        <p class="mt-1 text-sm text-slate-500">Create a new waiter account with contact and address details</p>
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

    <form action="{{ route('waiters.store') }}" method="POST" class="space-y-6">
        @csrf

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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="Juan Dela Cruz">
                        @error('name')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="juan@example.com">
                        @error('email')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Phone <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('phone') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            placeholder="0917 123 4567">
                        @error('phone')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Emergency Contact</label>
                        <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
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
                                    <option value="{{ $region->region_id }}" {{ old('region_id') == $region->region_id ? 'selected' : '' }}>
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
                            <select name="province_id" id="province" required disabled
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('province_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Province</option>
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
                            <select name="city_id" id="city" required disabled
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('city_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select City</option>
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
                            <select name="barangay_code" id="barangay" required disabled
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed @error('barangay_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">Select Barangay</option>
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
                        placeholder="House number, street name, building, subdivision...">{{ old('address') }}</textarea>
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

            <div class="p-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Date Hired</label>
                    <input type="date" name="date_hired" value="{{ old('date_hired') }}"
                        class="w-full sm:w-64 rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('date_hired') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                    @error('date_hired')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                                placeholder="••••••••">
                            <button type="button" onclick="toggleEye('password', 'passEye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                <svg class="w-4 h-4" id="passEye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Confirm Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-2.5 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-500/20 transition-all"
                                placeholder="••••••••">
                            <button type="button" onclick="toggleEye('password_confirmation', 'confirmEye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-amber-600 transition-colors focus:outline-none">
                                <svg class="w-4 h-4" id="confirmEye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('waiters.index') }}"
                class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-600/20 hover:bg-amber-700 hover:shadow-md hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Add Waiter
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const region = document.getElementById('region');
        const province = document.getElementById('province');
        const city = document.getElementById('city');
        const barangay = document.getElementById('barangay');
        const oldProvince = "{{ old('province_id') }}";
        const oldCity = "{{ old('city_id') }}";
        const oldBarangay = "{{ old('barangay_code') }}";

        region.addEventListener('change', function () {
            const rid = this.value;
            province.innerHTML = '<option value="">Select Province</option>';
            city.innerHTML = '<option value="">Select City</option>'; city.disabled = true;
            barangay.innerHTML = '<option value="">Select Barangay</option>'; barangay.disabled = true;
            if (!rid) { province.disabled = true; return; }
            province.disabled = false;
            fetch(`/api/provinces/${rid}`)
                .then(r => r.json()).then(data => {
                    data.forEach(p => {
                        let o = document.createElement('option'); o.value = p.id; o.textContent = p.name;
                        if (p.id === oldProvince) o.selected = true;
                        province.appendChild(o);
                    });
                    if (oldProvince) province.dispatchEvent(new Event('change'));
                });
        });

        province.addEventListener('change', function () {
            const pid = this.value;
            city.innerHTML = '<option value="">Select City</option>';
            barangay.innerHTML = '<option value="">Select Barangay</option>'; barangay.disabled = true;
            if (!pid) { city.disabled = true; return; }
            city.disabled = false;
            fetch(`/api/cities/${pid}`)
                .then(r => r.json()).then(data => {
                    data.forEach(c => {
                        let o = document.createElement('option'); o.value = c.id; o.textContent = c.name;
                        if (c.id === oldCity) o.selected = true;
                        city.appendChild(o);
                    });
                    if (oldCity) city.dispatchEvent(new Event('change'));
                });
        });

        city.addEventListener('change', function () {
            const cid = this.value;
            barangay.innerHTML = '<option value="">Select Barangay</option>';
            if (!cid) { barangay.disabled = true; return; }
            barangay.disabled = false;
            fetch(`/api/barangays/${cid}`)
                .then(r => r.json()).then(data => {
                    data.forEach(b => {
                        let o = document.createElement('option'); o.value = b.id; o.textContent = b.name;
                        if (b.id === oldBarangay) o.selected = true;
                        barangay.appendChild(o);
                    });
                });
        });

        if (region.value) region.dispatchEvent(new Event('change'));
    });

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
</script>
@endsection