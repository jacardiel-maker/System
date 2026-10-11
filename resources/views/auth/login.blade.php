<x-guest-layout>

    <!-- Logo & Brand -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 mb-4 animate-float">
            <i class="fas fa-calendar-check text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">E. Serve</h1>
        <p class="text-sm text-slate-400 mt-1">Event Scheduling & Equipment Tracking</p>
    </div>

    <!-- Login Card -->
    <div class="glass rounded-3xl shadow-2xl shadow-black/20 p-8">
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-800">Welcome back</h2>
            <p class="text-sm text-slate-500 mt-1">Sign in to your account to continue</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div class="input-group">
                <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 transition-colors">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-slate-400 text-sm transition-colors"></i>
                    </div>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                    >
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-500" />
            </div>

            <!-- Password -->
            <div class="input-group">
                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 transition-colors">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-400 text-sm transition-colors"></i>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                    >
                    <button 
                        type="button" 
                        id="togglePassword" 
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-amber-600 transition-colors focus:outline-none"
                    >
                        <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-500" />
            </div>

            <!-- Submit -->
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-semibold rounded-xl shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            >
                Sign In
            </button>

        </form>
    </div>

    <!-- Footer -->
    <div class="text-center mt-6">
        <p class="text-xs text-slate-500">
            &copy; {{ date('Y') }} E. Handog. All rights reserved.
        </p>
    </div>

    <!-- Password Toggle Script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>

</x-guest-layout>