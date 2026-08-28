<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-6">
            <x-input-label for="email" :value="__('Email Address')" class="text-sm font-semibold text-slate-700 mb-2 block" />
            <x-text-input id="email" class="input-modern block w-full rounded-xl px-4 py-3 text-sm bg-slate-50" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500" />
        </div>

        <div class="mb-6">
            <x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-slate-700 mb-2 block" />
            <div class="relative">
                <x-text-input id="password" class="input-modern block w-full rounded-xl px-4 py-3 pr-10 text-sm bg-slate-50" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-orange-500 transition cursor-pointer">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold py-3.5 rounded-xl hover:shadow-lg hover:shadow-orange-500/30 transition transform hover:-translate-y-0.5">
            {{ __('Sign In') }}
        </button>
    </form>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</x-guest-layout>