@extends('layouts.app')

@section('title', 'Kelola Menu')

@section('content')
<section class="min-h-[80vh] bg-[#f9f4eb] px-4 py-7 sm:px-6 sm:py-10">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Pengaturan warung</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Kelola Menu</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600 sm:text-base">Tambah menu, atur kategori dan stok awal, atau ubah deskripsi, harga, dan foto menu yang sudah tersimpan.</p>
            </div>
            <a href="{{ auth()->user()->role === 'owner' ? route('dashboard') : route('staff.menus') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-5 text-sm font-bold text-slate-800 hover:border-amber-400">Kembali</a>
        </div>

        <div class="mt-7 grid items-start gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Menu baru</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Tambah item menu</h2>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <p class="font-bold">Mohon periksa kembali:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('menus.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama menu</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Contoh: Nasi Kuning Komplit" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>

                    <div>
                        <label for="category_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori yang tersedia</label>
                        <select id="category_id" name="category_id" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                            <option value="">Pilih kategori atau buat kategori baru</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Atau nama kategori baru</label>
                        <input id="category_name" name="category_name" value="{{ old('category_name') }}" maxlength="120" placeholder="Contoh: Paket Nasi" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="price" class="mb-1.5 block text-sm font-semibold text-slate-700">Harga (Rp)</label>
                            <input id="price" name="price" type="number" value="{{ old('price') }}" min="1" step="1" inputmode="numeric" required placeholder="18000" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        </div>
                        <div>
                            <label for="image_url" class="mb-1.5 block text-sm font-semibold text-slate-700">URL foto <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="image_url" name="image_url" type="url" value="{{ old('image_url') }}" placeholder="https://..." class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="description" name="description" rows="3" maxlength="2000" placeholder="Ceritakan isi atau keistimewaan menu" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">{{ old('description') }}</textarea>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-sm font-bold text-slate-900">Stok awal hari ini</p>
                        <p class="mt-1 text-xs leading-5 text-slate-600">Stok akan langsung tersedia untuk dipesan setelah menu dibuat.</p>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="initial_stock" class="mb-1.5 block text-sm font-semibold text-slate-700">Jumlah porsi</label>
                                <input id="initial_stock" name="initial_stock" type="number" value="{{ old('initial_stock', 0) }}" min="0" step="1" inputmode="numeric" required class="min-h-12 w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-base text-slate-900 outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100">
                            </div>
                            <div>
                                <label for="minimum_stock" class="mb-1.5 block text-sm font-semibold text-slate-700">Peringatan minimum</label>
                                <input id="minimum_stock" name="minimum_stock" type="number" value="{{ old('minimum_stock', 5) }}" min="0" step="1" inputmode="numeric" required class="min-h-12 w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-base text-slate-900 outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100">
                            </div>
                        </div>
                    </div>

                    <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', true)) class="h-5 w-5 rounded border-slate-300 accent-amber-500">
                        <span class="text-sm font-semibold text-slate-700">Tampilkan sebagai menu aktif</span>
                    </label>

                    <button type="submit" class="min-h-12 w-full rounded-full bg-amber-500 px-6 py-3 font-bold text-slate-900 shadow-md shadow-amber-500/20 transition hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-200">Simpan menu & stok</button>
                </form>
            </section>

            <section class="min-w-0 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Daftar katalog</p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">Menu tersimpan</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $menus->count() }} menu</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($menus as $menu)
                        @php($todayStock = $menu->dailyStocks->first())
                        <article class="flex min-w-0 gap-4 rounded-2xl border border-slate-200 p-4">
                            @if ($menu->image_url)
                                <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-16 w-16 shrink-0 rounded-xl object-cover">
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-2xl">🍛</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="truncate font-bold text-slate-900">{{ $menu->name }}</h3>
                                    <span class="rounded-full {{ $menu->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }} px-2.5 py-1 text-[11px] font-bold">{{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $menu->category->name ?? 'Tanpa kategori' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                                    <span class="font-bold text-amber-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                    <span class="text-slate-600">Stok: <strong class="text-slate-900">{{ $todayStock?->quantity ?? 0 }}</strong></span>
                                </div>
                                <details class="mt-4 border-t border-slate-100 pt-3">
                                    <summary class="cursor-pointer text-sm font-bold text-amber-700">Edit deskripsi, harga, foto</summary>
                                    <form method="POST" action="{{ route('menus.update', $menu) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label for="description-{{ $menu->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Deskripsi</label>
                                            <textarea id="description-{{ $menu->id }}" name="description" rows="3" maxlength="2000" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">{{ $menu->description }}</textarea>
                                        </div>
                                        <div>
                                            <label for="price-{{ $menu->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Harga (Rp)</label>
                                            <input id="price-{{ $menu->id }}" name="price" type="number" value="{{ $menu->price }}" min="1" step="1" required class="min-h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                                        </div>
                                        <div>
                                            <label for="image_url-{{ $menu->id }}" class="mb-1 block text-xs font-semibold text-slate-700">URL foto</label>
                                            <input id="image_url-{{ $menu->id }}" name="image_url" type="url" value="{{ $menu->image_url }}" placeholder="https://..." class="min-h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                                        </div>
                                        <div>
                                            <label for="image-{{ $menu->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Atau unggah foto baru (maks. 5 MB)</label>
                                            <input id="image-{{ $menu->id }}" name="image" type="file" accept="image/*" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:font-semibold">
                                        </div>
                                        <button type="submit" class="min-h-10 rounded-full bg-amber-500 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-400">Simpan perubahan</button>
                                    </form>
                                </details>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">Belum ada menu. Tambahkan menu pertama melalui form.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
