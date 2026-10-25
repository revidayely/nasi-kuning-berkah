<x-guest-layout>
    <div class="mb-6 text-center">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500 text-2xl shadow-md shadow-amber-500/30">🍚</span>
        <h2 class="mt-3 text-2xl font-black text-slate-900">Masuk ke Akun Anda</h2>
        <p class="mt-1 text-xs text-slate-500">Silakan masukkan email dan password untuk melanjutkan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="font-bold text-xs uppercase tracking-wider text-slate-700" />
            <x-text-input id="email" class="block mt-1.5 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Kata Sandi')" class="font-bold text-xs uppercase tracking-wider text-slate-700" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-amber-700 hover:text-amber-900" href="{{ route('password.request') }}">
                        {{ __('Lupa password?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-1.5 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-amber-600 shadow-sm focus:ring-amber-500" name="remember">
                <span class="ms-2 text-xs font-semibold text-slate-600">{{ __('Ingat saya di perangkat ini') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full rounded-xl bg-slate-900 py-3 text-sm font-black text-white transition hover:bg-amber-500 hover:text-slate-900 shadow-md">
                {{ __('Masuk') }}
            </button>
        </div>

        <div class="text-center pt-3 border-t border-slate-100">
            <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                ← Kembali ke Beranda
            </a>
        </div>
    </form>
</x-guest-layout>
