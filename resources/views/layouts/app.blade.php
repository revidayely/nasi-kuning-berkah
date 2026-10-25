<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Warung Nasi Kuning Berkah'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f9f4eb] text-slate-900 antialiased font-sans">
    @php
        $brandUrl = auth()->check()
            ? (auth()->user()->role === 'owner' ? route('dashboard') : (auth()->user()->role === 'cashier' ? route('pos.index') : route('home')))
            : route('home');
    @endphp
    <div class="min-h-screen flex flex-col justify-between">
        <div>
            <header class="sticky top-0 z-30 border-b border-amber-200/70 bg-white/95 shadow-sm backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-5 sm:px-6">
                    <a href="{{ $brandUrl }}" class="flex min-h-11 shrink-0 items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-xl shadow-md shadow-amber-500/25">🍚</span>
                        <span>
                            <span class="block text-base font-black tracking-tight text-slate-900">Warung Nasi Kuning Berkah</span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-amber-700">Autentik & Cita Rasa Berkah</span>
                        </span>
                    </a>
                    <nav class="-mx-1 flex items-center gap-1 overflow-x-auto px-1 pb-1 text-xs font-bold text-slate-700 sm:mx-0 sm:gap-2 sm:overflow-visible sm:pb-0 md:text-sm">
                        @auth
                            @if (in_array(auth()->user()->role, ['owner', 'cashier']))
                                <a href="{{ route('pos.index') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('pos.*') ? 'bg-amber-100 text-amber-900' : '' }}">Kasir POS</a>
                                <a href="{{ route('stock.index') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('stock.*') ? 'bg-amber-100 text-amber-900' : '' }}">Stok Harian</a>
                                <a href="{{ route('staff.menus') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('staff.menus') ? 'bg-amber-100 text-amber-900' : '' }}">Daftar Menu</a>
                                @if (auth()->user()->role === 'owner')
                                    <a href="{{ route('dashboard') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('dashboard*') ? 'bg-amber-100 text-amber-900' : '' }}">Dashboard Laporan</a>
                                    <a href="{{ route('staff.manage') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('staff.manage*') ? 'bg-amber-100 text-amber-900' : '' }}">Kelola Staf</a>
                                    <a href="{{ route('menus.manage') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800 {{ request()->routeIs('menus.manage*') ? 'bg-amber-100 text-amber-900' : '' }}">+ Menu</a>
                                @endif
                            @else
                                <a href="{{ route('home') }}" class="min-h-10 shrink-0 rounded-full px-3.5 py-2 hover:bg-amber-100 hover:text-amber-900">Beranda</a>
                                <a href="{{ route('menu') }}#menu" class="min-h-10 shrink-0 rounded-full px-3.5 py-2 hover:bg-amber-100 hover:text-amber-900">Pilihan Menu</a>
                                <a href="{{ route('cart.index') }}" id="navCartLink" class="min-h-10 shrink-0 rounded-full bg-amber-500 px-4 py-2 text-slate-900 shadow-sm transition hover:bg-amber-400">Keranjang</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="min-h-10 shrink-0 rounded-full px-3 py-2 hover:bg-amber-100 hover:text-amber-800">Profil ({{ auth()->user()->name }})</a>
                            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                                @csrf
                                <button class="min-h-10 rounded-full bg-slate-900 px-4 py-2 font-bold text-white transition hover:bg-amber-500 hover:text-slate-900">Keluar</button>
                            </form>
                        @else
                            <a href="{{ route('home') }}" class="min-h-10 shrink-0 rounded-full px-3.5 py-2 hover:bg-amber-100 hover:text-amber-900">Beranda</a>
                            <a href="{{ route('menu') }}#menu" class="min-h-10 shrink-0 rounded-full px-3.5 py-2 hover:bg-amber-100 hover:text-amber-900">Pilihan Menu</a>
                            <a href="{{ route('cart.index') }}" id="navCartLink" class="min-h-10 shrink-0 rounded-full bg-amber-500 px-4 py-2 text-slate-900 shadow-sm transition hover:bg-amber-400">Keranjang</a>
                            <a href="{{ route('login') }}" class="min-h-10 shrink-0 rounded-full bg-slate-900 px-5 py-2 font-bold text-white transition hover:bg-amber-500 hover:text-slate-900">Masuk</a>
                        @endauth
                    </nav>
                </div>
            </header>

            @if (session('success') || session('error'))
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6">
                    <div role="status" class="rounded-2xl border px-4 py-3 text-sm font-semibold {{ session('error') ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
                        {{ session('success') ?? session('error') }}
                    </div>
                </div>
            @endif

            <main>@yield('content')</main>
        </div>

        <!-- Footer -->
        <footer class="mt-16 border-t border-amber-200/70 bg-white py-12 text-slate-700">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-xl shadow-md shadow-amber-500/25">🍚</span>
                            <span class="text-base font-black text-slate-900">Warung Nasi Kuning Berkah</span>
                        </div>
                        <p class="mt-3 text-xs leading-relaxed text-slate-600">
                            Nasi kuning pulen kaya rempah kunyit santan, aneka lauk gurih rumahan, dan sambal khas warung. Hangat mengepul setiap fajar.
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-900">Lokasi & Operasional</p>
                        <p class="mt-2 text-xs text-slate-600">Jl. Nasi Kuning Berkah No. 12, Bandung</p>
                        <p class="mt-1 text-xs text-slate-600">Setiap hari: 05.30 - 13.00 WIB</p>
                        <p class="mt-1 text-xs text-slate-600">WhatsApp: {{ config('services.warung.whatsapp', '6287848361913') }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-900">Sistem POS & Pemesanan</p>
                        <p class="mt-2 text-xs text-slate-600">
                            Didukung Laravel 12, Tailwind CSS, & MySQL. Transaksi cepat, cetak struk thermal, dan pemantauan stok otomatis.
                        </p>
                        <p class="mt-3 text-[11px] text-slate-400">© 2026 Warung Nasi Kuning Berkah. Hak cipta dilindungi.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
