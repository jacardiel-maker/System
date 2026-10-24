@extends('layouts.app')
@section('title', 'My Account')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">My Account</h1>
                <p class="text-slate-400 text-sm">Your login details and password</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="self-start sm:self-auto px-3 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Dashboard
            </a>
        </div>

        <div class="space-y-4">
            {{-- Account details --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Account</h2>
                    <p class="text-xs text-slate-400">You have view-only access to the Dashboard and Reports</p>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Name</p>
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-envelope text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Email</p>
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Change password --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Security</h2>
                    <p class="text-xs text-slate-400">Update your account password</p>
                </div>

                <div class="p-4">
                    @if(session('password_success'))
                        <div class="mb-3 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 flex items-center gap-2 text-sm text-emerald-800">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span class="font-medium">{{ session('password_success') }}</span>
                        </div>
                    @endif

                    @if(session('password_error'))
                        <div class="mb-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2 flex items-center gap-2 text-sm text-red-800">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                            <span class="font-medium">{{ session('password_error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('owner.change-password') }}" method="POST" id="passwordForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach([
                                ['current_password', 'ownerCurrentPass', 'Current Password', 'current-password', null],
                                ['new_password', 'ownerNewPass', 'New Password', 'new-password', 'Minimum 6 characters'],
                                ['new_password_confirmation', 'ownerConfirmPass', 'Confirm Password', 'new-password', null],
                            ] as [$field, $id, $label, $autocomplete, $hint])
                                <div>
                                    <label for="{{ $id }}" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ $label }}</label>
                                    <div class="relative">
                                        <input type="password" name="{{ $field }}" id="{{ $id }}" required autocomplete="{{ $autocomplete }}"
                                            @if($field === 'new_password') minlength="6" @endif
                                            class="w-full pl-3 pr-9 py-1.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent {{ $errors->has($field) ? 'border-red-400' : 'border-slate-200' }}">
                                        <button type="button" onclick="toggleEye('{{ $id }}', this)" aria-label="Show password"
                                            class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-slate-400 hover:text-amber-600">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                    @error($field)
                                        <p class="mt-1 text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                    @else
                                        @if($hint)<p class="mt-1 text-[11px] text-slate-400">{{ $hint }}</p>@endif
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <p id="passwordMismatch" class="hidden mt-2 text-[11px] text-red-600 font-medium">Passwords do not match.</p>

                        <div class="mt-3">
                            <button type="submit" id="passwordSubmit"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-key text-xs"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEye(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = 'fas ' + (show ? 'fa-eye-slash' : 'fa-eye') + ' text-xs';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        }

        (function () {
            // Live check that the new password and confirmation match
            const newPass = document.getElementById('ownerNewPass');
            const confirmPass = document.getElementById('ownerConfirmPass');
            const mismatch = document.getElementById('passwordMismatch');
            const form = document.getElementById('passwordForm');
            const submit = document.getElementById('passwordSubmit');

            function check() {
                const bad = confirmPass.value.length > 0 && newPass.value !== confirmPass.value;
                mismatch.classList.toggle('hidden', !bad);
                confirmPass.classList.toggle('border-red-400', bad);
                return !bad;
            }

            newPass.addEventListener('input', check);
            confirmPass.addEventListener('input', check);

            form.addEventListener('submit', function (e) {
                if (!check()) { e.preventDefault(); confirmPass.focus(); return; }
                submit.disabled = true;
                submit.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Updating...';
            });
        })();
    </script>
@endsection