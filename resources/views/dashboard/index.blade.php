@extends('layouts.app')

@section('title', 'Dashboard Owner & Laporan Omset')

@section('content')
<section class="min-h-screen bg-[#f9f4eb] px-4 py-6 sm:px-6 sm:py-10">
    <div class="mx-auto max-w-7xl">
        <!-- Header & Action Buttons -->
        <div class="flex flex-col justify-between gap-4 border-b border-amber-200/60 pb-6 sm:flex-row sm:items-end">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-amber-800">Owner Analytics</span>
                    <span class="text-xs text-slate-500">Live Data</span>
                </div>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Laporan & Kinerja Warung</h1>
                <p class="mt-1 text-sm text-slate-600">Pantau omset harian, produk terlaris, dan rekapitulasi transaksi.</p>
            </div>

            <!-- Export & Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('dashboard.export', ['range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-sm transition hover:border-emerald-500 hover:text-emerald-700 sm:text-sm">
                    📥 Unduh CSV
                </a>
                <a href="{{ route('dashboard.print', ['range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" target="_blank" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-sm transition hover:border-amber-500 hover:text-amber-700 sm:text-sm">
                    🖨️ Cetak Rekap
                </a>
                <a href="{{ route('staff.manage') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-sm transition hover:border-amber-500 hover:text-amber-700 sm:text-sm">
                    👥 Kelola Staf
                </a>
                <a href="{{ route('pos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-slate-900 px-5 py-2 text-xs font-bold text-white transition hover:bg-slate-800 sm:text-sm">
                    Buka Kasir POS →
                </a>
            </div>
        </div>

        <!-- Filter Rentang Waktu -->
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Periode:</span>
                <a href="{{ route('dashboard', ['range' => 'today']) }}" class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $range === 'today' ? 'bg-amber-500 text-slate-900 shadow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Hari Ini</a>
                <a href="{{ route('dashboard', ['range' => 'yesterday']) }}" class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $range === 'yesterday' ? 'bg-amber-500 text-slate-900 shadow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Kemarin</a>
                <a href="{{ route('dashboard', ['range' => '7days']) }}" class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $range === '7days' ? 'bg-amber-500 text-slate-900 shadow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">7 Hari Terakhir</a>
                <a href="{{ route('dashboard', ['range' => 'this_month']) }}" class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $range === 'this_month' ? 'bg-amber-500 text-slate-900 shadow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Bulan Ini</a>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <input type="hidden" name="range" value="custom">
                <input type="date" name="start_date" value="{{ request('start_date', today()->toDateString()) }}" class="rounded-xl border border-slate-300 px-2.5 py-1 text-xs text-slate-800 focus:border-amber-500 focus:outline-none">
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date', today()->toDateString()) }}" class="rounded-xl border border-slate-300 px-2.5 py-1 text-xs text-slate-800 focus:border-amber-500 focus:outline-none">
                <button type="submit" class="rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800">Filter</button>
            </form>
        </div>

        <!-- 4 KPI Stat Cards -->
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5">
            <div class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Omset ({{ $rangeLabel }})</p>
                <p class="mt-2 text-2xl font-black text-amber-600 sm:text-3xl">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">Omset Hari Ini: Rp {{ number_format($dailyRevenue, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Transaksi Selesai</p>
                <p class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ $transactionCount }} <span class="text-sm font-semibold text-slate-500">pesanan</span></p>
                <p class="mt-1 text-xs text-slate-500">Total Hari Ini: {{ $transactionsToday }} dibuat</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Rata-rata Order (AOV)</p>
                <p class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">Bulan Ini: Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Antrean Perlu Aksi</p>
                <p class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">{{ $pendingOrders }} <span class="text-sm font-semibold text-slate-500">pending</span></p>
                <p class="mt-1 text-xs text-amber-700">{{ $qrisWaiting }} menunggu verifikasi QRIS</p>
            </div>
        </div>

        <!-- Tren Penjualan 7 Hari (Chart Bar Visual) -->
        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-600">Grafik Penjualan</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Tren Omset 7 Hari Terakhir</h2>
                </div>
                <div class="text-xs text-slate-500">Berdasarkan tanggal transaksi lunas</div>
            </div>

            <div class="mt-6 grid grid-cols-7 items-end gap-2 sm:gap-4 h-48 border-b border-slate-100 pb-3">
                @foreach ($dailyTrend as $trend)
                    @php
                        $percentage = $maxTrendOmset > 0 ? max(8, round(($trend['omset'] / $maxTrendOmset) * 100)) : 8;
                    @endphp
                    <div class="flex flex-col items-center h-full justify-end group">
                        <div class="text-[10px] sm:text-xs font-bold text-slate-600 opacity-0 group-hover:opacity-100 transition mb-1">
                            Rp {{ number_format($trend['omset'], 0, ',', '.') }}
                        </div>
                        <div class="w-full max-w-[48px] rounded-t-xl bg-gradient-to-t from-amber-500 to-amber-400 group-hover:from-amber-600 group-hover:to-amber-500 transition-all shadow-sm" style="height: {{ $percentage }}%;"></div>
                        <span class="mt-2 text-[10px] sm:text-xs font-semibold text-slate-600">{{ $trend['label'] }}</span>
                        <span class="text-[9px] text-slate-400">{{ $trend['count'] }} trx</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2 Columns: Pesanan Terbaru & Top 5 Menu -->
        <div class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <!-- Pesanan Terbaru -->
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 md:px-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-600">Operasional Warung</p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">Pesanan Terbaru</h2>
                    </div>
                    <a href="{{ route('pos.index') }}" class="text-sm font-bold text-amber-700 hover:text-amber-900">Buka Kasir POS →</a>
                </div>

                <!-- Tampilan Mobile Card -->
                <div class="space-y-3 p-4 md:hidden">
                    @forelse ($recentOrders as $order)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900">#{{ $order->id }} · {{ $order->customer_name ?: 'Pelanggan' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->details->sum('quantity') }} item ({{ $order->details->map(fn($d) => $d->menu?->name)->filter()->join(', ') }})</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $order->cashier?->name ?? 'Pesanan pelanggan dari website' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                    <a href="{{ route('pos.orders.receipt', $order) }}" target="_blank" class="mt-1 inline-block text-[11px] font-bold text-amber-700 hover:underline">Struk 🖨️</a>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $order->payment_method === 'qris' ? 'QRIS' : 'Di warung' }}</span>
                                <span class="rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} px-3 py-1 text-xs font-semibold">{{ $order->payment_status === 'paid' ? 'Lunas' : 'Menunggu' }}</span>
                                @php($mobileKitchenLabel = ['new' => 'Baru', 'preparing' => 'Disiapkan', 'ready' => 'Siap diambil', 'completed' => 'Selesai'][$order->kitchen_status] ?? $order->kitchen_status)
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">{{ $mobileKitchenLabel }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Belum ada pesanan.</p>
                    @endforelse
                </div>

                <!-- Tampilan Desktop Table -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-bold md:px-7">Pesanan</th>
                                <th class="px-5 py-3 font-bold">Dibuat oleh</th>
                                <th class="px-5 py-3 font-bold">Pembayaran</th>
                                <th class="px-5 py-3 font-bold">Status</th>
                                <th class="px-5 py-3 text-right font-bold md:px-7">Total & Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td class="px-5 py-4 md:px-7">
                                        <p class="font-bold text-slate-900">#{{ $order->id }} · {{ $order->customer_name ?: 'Pelanggan' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->details->sum('quantity') }} item ({{ $order->details->map(fn($d) => $d->menu?->name)->filter()->join(', ') }})</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ $order->cashier?->name ?? 'Pesanan pelanggan' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $order->cashier_id ? 'Kasir' : 'Website' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ $order->payment_method === 'qris' ? 'QRIS' : 'Di warung' }}</p>
                                        <p class="mt-1 text-xs {{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $order->payment_status === 'paid' ? 'Terkonfirmasi' : 'Menunggu' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        @php($kitchenLabel = ['new' => 'Baru', 'preparing' => 'Disiapkan', 'ready' => 'Siap diambil', 'completed' => 'Selesai'][$order->kitchen_status] ?? $order->kitchen_status)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $kitchenLabel }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right md:px-7">
                                        <p class="font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                        <a href="{{ route('pos.orders.receipt', $order) }}" target="_blank" class="mt-1 inline-block text-xs font-bold text-amber-700 hover:text-amber-900">Cetak Struk 🖨️</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Top 5 Menu Terlaris -->
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-600">Kinerja Produk</p>
                <h2 class="mt-1 text-xl font-black text-slate-900">Top 5 Menu Terlaris</h2>
                <p class="mt-0.5 text-xs text-slate-500">Periode: {{ $rangeLabel }}</p>

                <div class="mt-5 space-y-3">
                    @forelse ($topMenus as $index => $menu)
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 p-4 transition hover:bg-amber-50/60">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-sm font-black text-slate-900">{{ $index + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900">{{ $menu->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $menu->category->name ?? 'Menu' }} · Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-800 shadow-sm">{{ $menu->sold_quantity }} porsi</span>
                                @if (isset($menu->menu_revenue))
                                    <p class="mt-1 text-[11px] font-bold text-amber-700">Rp {{ number_format($menu->menu_revenue, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">Belum ada transaksi lunas pada periode ini.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- Peringatan Stok Lauk -->
        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-600">Perhatian Inventori</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Peringatan Stok Lauk Harian</h2>
                </div>
                <a href="{{ route('stock.index') }}" class="text-sm font-bold text-amber-700 hover:text-amber-900">Kelola & Tambah Stok →</a>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($stockAlerts as $stock)
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-red-900">{{ $stock->menu->name }}</p>
                            <span class="rounded-full bg-red-200 px-2.5 py-0.5 text-[10px] font-black text-red-800">MENIPIS</span>
                        </div>
                        <p class="mt-2 text-sm text-red-700">Sisa <strong>{{ $stock->quantity }} porsi</strong> · Batas minimum {{ $stock->minimum_stock }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl bg-emerald-50 p-5 text-sm text-emerald-800 sm:col-span-2 xl:col-span-4 flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <div>
                            <p class="font-bold">Semua stok menu aman</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Tidak ada menu yang berada di bawah ambang batas minimum hari ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection
