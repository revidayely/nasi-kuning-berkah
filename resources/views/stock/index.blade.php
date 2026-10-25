@extends('layouts.app')

@section('title', 'Inventori & Kelola Stok Harian')

@section('content')
@php
    $totalMenus = $stocks->count();
    $safeCount = $stocks->filter(fn($s) => $s->quantity > $s->minimum_stock)->count();
    $warningCount = $stocks->filter(fn($s) => $s->quantity <= $s->minimum_stock && $s->quantity > 0)->count();
    $emptyCount = $stocks->filter(fn($s) => $s->quantity == 0)->count();
@endphp

<section class="min-h-[85vh] bg-[#f9f4eb] px-3 py-4 sm:px-6 sm:py-8">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col justify-between gap-4 border-b border-amber-200/60 pb-5 sm:flex-row sm:items-end">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-amber-800">Manajemen Inventori</span>
                    <span class="text-xs text-slate-500">Tanggal: {{ now()->format('d M Y') }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Stok Bahan Baku & Lauk Harian</h1>
                <p class="mt-1 text-sm text-slate-600">Pantau stok, atur porsi siap saji pagi, dan jaga ketersediaan rasa untuk tamu.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" class="rounded-full border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-800 shadow-sm transition hover:border-amber-400 sm:text-sm">
                    ← Kembali ke Kasir POS
                </a>
            </div>
        </div>

        <!-- 4 KPI Summary Cards (Matching Screen 3) -->
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Menu</p>
                <p class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ $totalMenus }} <span class="text-xs font-semibold text-slate-500">menu</span></p>
                <p class="mt-1 text-xs text-slate-400">Tersedia hari ini</p>
            </div>

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">Stok Aman</p>
                <p class="mt-2 text-2xl font-black text-emerald-700 sm:text-3xl">{{ $safeCount }} <span class="text-xs font-semibold text-emerald-600">menu</span></p>
                <p class="mt-1 text-xs text-emerald-600">Di atas batas minimum</p>
            </div>

            <div class="rounded-3xl border border-amber-200 bg-amber-50/60 p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Butuh Perhatian</p>
                <p class="mt-2 text-2xl font-black text-amber-600 sm:text-3xl">{{ $warningCount }} <span class="text-xs font-semibold text-amber-700">menu</span></p>
                <p class="mt-1 text-xs text-amber-700">Mendekati batas limit</p>
            </div>

            <div class="rounded-3xl border border-red-200 bg-red-50/50 p-4 shadow-sm sm:p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-red-800">Stok Habis</p>
                <p class="mt-2 text-2xl font-black text-red-600 sm:text-3xl">{{ $emptyCount }} <span class="text-xs font-semibold text-red-500">menu</span></p>
                <p class="mt-1 text-xs text-red-600">Segera restok lauk</p>
            </div>
        </div>

        @if ($warningCount > 0 || $emptyCount > 0)
            <div class="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900 sm:text-sm">
                <span class="text-xl">⚠️</span>
                <div>
                    <strong>Pemberitahuan Stok:</strong> Ada {{ $warningCount + $emptyCount }} menu yang menipis atau habis. Siapkan penambahan porsi dapur untuk menjaga kelancaran pesanan jam makan siang.
                </div>
            </div>
        @endif

        <!-- Filter & Search Controls -->
        <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1">
                <input 
                    type="text" 
                    id="stockSearch" 
                    placeholder="🔍 Cari nama lauk atau menu..." 
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-800 focus:border-amber-500 focus:bg-white focus:outline-none sm:text-sm"
                    oninput="filterStockTable()"
                >
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Status:</span>
                <select id="statusFilter" onchange="filterStockTable()" class="rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-amber-500 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="safe">Stok Aman</option>
                    <option value="warning">Menipis</option>
                    <option value="empty">Habis</option>
                </select>
            </div>
        </div>

        <!-- Form Update Stok Table -->
        <form method="POST" action="{{ route('stock.store') }}" class="mt-4">
            @csrf
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-slate-900 text-xs uppercase tracking-wider text-slate-300">
                            <tr>
                                <th class="px-6 py-4 font-bold">Nama Menu / Lauk</th>
                                <th class="px-6 py-4 font-bold">Kategori</th>
                                <th class="px-6 py-4 font-bold">Stok Saat Ini (Porsi)</th>
                                <th class="px-6 py-4 font-bold">Batas Minimum</th>
                                <th class="px-6 py-4 font-bold">Status</th>
                                <th class="px-6 py-4 font-bold text-right">Diperbarui</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody" class="divide-y divide-slate-100">
                            @forelse ($stocks as $stock)
                                @php
                                    $status = $stock->quantity > $stock->minimum_stock ? 'safe' : ($stock->quantity > 0 ? 'warning' : 'empty');
                                @endphp
                                <tr class="stock-row transition hover:bg-slate-50/80" data-name="{{ strtolower($stock->menu->name) }}" data-status="{{ $status }}">
                                    <!-- Nama Menu -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($stock->menu->image_url)
                                                <img src="{{ $stock->menu->image_url }}" alt="{{ $stock->menu->name }}" class="h-10 w-10 rounded-xl object-cover shrink-0">
                                            @endif
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $stock->menu->name }}</p>
                                                <p class="text-xs text-amber-700 font-semibold">Rp {{ number_format($stock->menu->price, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kategori -->
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ $stock->menu->category->name ?? 'Menu' }}
                                        </span>
                                    </td>

                                    <!-- Input Stok Tersedia -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="adjustInput('quantity-{{ $stock->menu_id }}', -5)" class="h-8 w-8 rounded-lg bg-slate-100 text-xs font-bold text-slate-700 hover:bg-slate-200">-5</button>
                                            <input 
                                                id="quantity-{{ $stock->menu_id }}" 
                                                type="number" 
                                                name="stocks[{{ $stock->menu_id }}][quantity]" 
                                                value="{{ $stock->quantity }}" 
                                                min="0" 
                                                required 
                                                class="w-24 rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-center text-sm font-bold text-slate-900 focus:border-amber-500 focus:bg-white focus:outline-none"
                                            >
                                            <button type="button" onclick="adjustInput('quantity-{{ $stock->menu_id }}', 5)" class="h-8 w-8 rounded-lg bg-slate-100 text-xs font-bold text-slate-700 hover:bg-slate-200">+5</button>
                                        </div>
                                    </td>

                                    <!-- Input Minimum Stok -->
                                    <td class="px-6 py-4">
                                        <input 
                                            id="minimum-{{ $stock->menu_id }}" 
                                            type="number" 
                                            name="stocks[{{ $stock->menu_id }}][minimum_stock]" 
                                            value="{{ $stock->minimum_stock }}" 
                                            min="0" 
                                            required 
                                            class="w-20 rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-center text-sm font-semibold text-slate-900 focus:border-amber-500 focus:bg-white focus:outline-none"
                                        >
                                    </td>

                                    <!-- Status Indicator Badge -->
                                    <td class="px-6 py-4">
                                        @if ($stock->quantity > $stock->minimum_stock)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Aman
                                            </span>
                                        @elseif ($stock->quantity > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Menipis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">
                                                <span class="h-2 w-2 rounded-full bg-red-500"></span> Habis
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Terakhir Diperbarui -->
                                    <td class="px-6 py-4 text-right text-xs text-slate-400">
                                        {{ (!empty($stock->updated_at)) ? $stock->updated_at->format('H:i') . ' WIB' : 'Pagi ini' }}
                                    </td>

                                    <input type="hidden" name="stocks[{{ $stock->menu_id }}][menu_id]" value="{{ $stock->menu_id }}">
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">Belum ada menu terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Bar Simpan -->
                <div class="flex flex-col justify-between gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center">
                    <p class="text-xs text-slate-500">
                        💡 Stok lauk otomatis berkurang saat kasir menyelesaikan transaksi POS atau mengonfirmasi pembayaran online.
                    </p>
                    <button type="submit" class="rounded-full bg-amber-500 px-8 py-3 text-sm font-black text-slate-900 shadow-md shadow-amber-500/20 transition hover:bg-amber-400">
                        💾 Simpan Perubahan Stok Hari Ini
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
    function adjustInput(id, delta) {
        const input = document.getElementById(id);
        if (input) {
            let val = parseInt(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val;
        }
    }

    function filterStockTable() {
        const query = document.getElementById('stockSearch').value.toLowerCase().trim();
        const status = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('.stock-row');

        rows.forEach(row => {
            const name = row.dataset.name;
            const rowStatus = row.dataset.status;
            const matchQuery = name.includes(query);
            const matchStatus = (status === 'all' || rowStatus === status);

            if (matchQuery && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
