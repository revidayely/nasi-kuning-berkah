@extends('layouts.app')

@section('title', 'Menu & Stok')

@section('content')
<section class="min-h-[80vh] bg-[#f9f4eb] px-4 py-6 sm:px-6 sm:py-10">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Katalog operasional</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Menu & Stok</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Pantau ketersediaan menu terbaru. Perubahan stok yang disimpan akan tampil di sini.</p>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('stock.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-4 text-center text-sm font-bold text-slate-800 hover:border-amber-400">Perbarui stok</a>
                <a href="{{ route('menus.manage') }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-amber-500 px-4 text-center text-sm font-bold text-slate-900 hover:bg-amber-400">Kelola menu</a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs text-slate-500 sm:text-sm">Jumlah menu</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $menus->count() }}</p></article>
            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm"><p class="text-xs text-emerald-800 sm:text-sm">Menu tersedia</p><p class="mt-2 text-2xl font-black text-emerald-900">{{ $menus->filter(fn ($menu) => $menu->is_available && (($menu->dailyStocks->first()?->quantity ?? 0) > 0))->count() }}</p></article>
            <article class="col-span-2 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm sm:col-span-1"><p class="text-xs text-red-800 sm:text-sm">Stok habis</p><p class="mt-2 text-2xl font-black text-red-900">{{ $menus->filter(fn ($menu) => ($menu->dailyStocks->first()?->quantity ?? 0) === 0)->count() }}</p></article>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($menus as $menu)
                @php($stock = $menu->dailyStocks->first()?->quantity ?? 0)
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    @if ($menu->image_url)
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-40 w-full object-cover sm:h-48">
                    @else
                        <div class="flex h-32 items-center justify-center bg-gradient-to-br from-amber-100 to-orange-50 text-5xl sm:h-40">🍛</div>
                    @endif
                    <div class="p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ $menu->category->name ?? 'Tanpa kategori' }}</p><h2 class="mt-1 break-words text-lg font-black text-slate-900">{{ $menu->name }}</h2></div>
                            <span class="shrink-0 rounded-full {{ $menu->is_available && $stock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }} px-3 py-1 text-xs font-bold">{{ $menu->is_available && $stock > 0 ? 'Tersedia' : 'Habis' }}</span>
                        </div>
                        <p class="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-slate-600">{{ $menu->description ?: 'Tidak ada deskripsi.' }}</p>
                        <div class="mt-4 flex items-end justify-between border-t border-slate-100 pt-4">
                            <div><p class="text-xs text-slate-500">Harga</p><p class="text-lg font-black text-amber-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</p></div>
                            <div class="text-right"><p class="text-xs text-slate-500">Stok hari ini</p><p class="text-xl font-black {{ $stock > 0 ? 'text-slate-900' : 'text-red-700' }}">{{ $stock }} <span class="text-xs font-semibold">porsi</span></p></div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center sm:col-span-2 xl:col-span-3"><p class="text-4xl">🍽️</p><h2 class="mt-3 font-bold text-slate-900">Belum ada menu</h2><p class="mt-1 text-sm text-slate-600">Owner dapat menambahkan menu melalui menu admin.</p></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
