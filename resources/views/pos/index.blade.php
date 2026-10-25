@extends('layouts.app')

@section('title', 'Kasir POS - Warung Nasi Kuning Berkah')

@section('content')
<section class="min-h-[88vh] bg-[#f9f4eb] px-3 py-4 sm:px-6 sm:py-8">
    <div class="mx-auto max-w-7xl">
        <!-- Top Bar Navigation & Mode Switcher -->
        <div class="flex flex-col justify-between gap-4 border-b border-amber-200/60 pb-5 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-500 px-3 py-0.5 text-xs font-black text-slate-900">POS KASIR</span>
                    <span class="text-xs text-slate-500">Kasir: {{ auth()->user()->name }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Pelayanan Cepat & Akurat</h1>
            </div>

            <!-- Mode Selector Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <button 
                    type="button" 
                    id="tabPosBtn" 
                    onclick="switchTab('pos')" 
                    class="inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 text-xs font-bold transition shadow-sm bg-slate-900 text-white sm:text-sm"
                >
                    <span>⚡ Kasir POS Langsung</span>
                </button>
                <button 
                    type="button" 
                    id="tabQueueBtn" 
                    onclick="switchTab('queue')" 
                    class="inline-flex min-h-11 items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2 text-xs font-bold text-slate-800 transition hover:border-amber-400 sm:text-sm"
                >
                    <span>📋 Antrean Pesanan</span>
                    @if ($orders->count() > 0)
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[11px] font-black text-slate-900">{{ $orders->count() }}</span>
                    @endif
                </button>
                <a href="{{ route('stock.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-800 transition hover:border-amber-400 sm:text-sm">
                    📦 Kelola Stok
                </a>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: TERMINAL KASIR POS LANGSUNG (GRID + CART) -->
        <!-- ========================================== -->
        <div id="posView" class="mt-6 grid gap-6 lg:grid-cols-[1.3fr_0.9fr]">
            <!-- Sisi Kiri: Menu Grid & Pencarian -->
            <div>
                <!-- Bar Pencarian & Kategori -->
                <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            id="menuSearch" 
                            placeholder="🔍 Cari menu lauk atau minuman..." 
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-900 focus:border-amber-500 focus:bg-white focus:outline-none"
                            oninput="filterMenus()"
                        >
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto">
                        <button type="button" onclick="filterCategory('all', this)" class="category-btn rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition">Semua</button>
                        @foreach ($categories as $cat)
                            <button type="button" onclick="filterCategory('{{ $cat->id }}', this)" class="category-btn rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-200">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>

                <!-- Grid Menu Cards -->
                <div id="menusGrid" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                    @forelse ($menus as $m)
                        @php
                            $todayStock = $m->dailyStocks->first()?->quantity ?? 0;
                            $minStock = $m->dailyStocks->first()?->minimum_stock ?? 5;
                            $isAvailable = $todayStock > 0;
                        @endphp
                        <article 
                            class="menu-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-amber-400 hover:shadow-md"
                            data-name="{{ strtolower($m->name) }}"
                            data-category="{{ $m->category_id }}"
                        >
                            <div class="relative">
                                <img src="{{ $m->image_url ?? 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $m->name }}" class="h-28 w-full rounded-xl object-cover sm:h-32">
                                <span class="absolute left-2 top-2 rounded-lg bg-black/60 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-sm">
                                    {{ $m->category->name ?? 'Menu' }}
                                </span>
                                <span class="absolute right-2 top-2 rounded-lg px-2 py-0.5 text-[10px] font-black {{ $todayStock > $minStock ? 'bg-emerald-500 text-white' : ($todayStock > 0 ? 'bg-amber-500 text-slate-900' : 'bg-red-500 text-white') }}">
                                    {{ $todayStock > 0 ? "Sisa {$todayStock}" : 'Habis' }}
                                </span>
                            </div>

                            <div class="mt-2.5 flex-1">
                                <h3 class="font-bold text-slate-900 line-clamp-1 text-sm sm:text-base">{{ $m->name }}</h3>
                                <p class="mt-1 text-sm font-black text-amber-700">Rp {{ number_format($m->price, 0, ',', '.') }}</p>
                            </div>

                            <button 
                                type="button" 
                                onclick="addToCart({{ $m->id }}, '{{ addslashes($m->name) }}', {{ $m->price }}, {{ $todayStock }})"
                                class="mt-3 w-full rounded-xl py-2 text-xs font-bold transition {{ $isAvailable ? 'bg-slate-900 text-white hover:bg-amber-500 hover:text-slate-900' : 'cursor-not-allowed bg-slate-200 text-slate-400' }}"
                                {{ $isAvailable ? '' : 'disabled' }}
                            >
                                {{ $isAvailable ? '+ Tambah' : 'Stok Habis' }}
                            </button>
                        </article>
                    @empty
                        <div class="col-span-3 rounded-2xl bg-white p-8 text-center text-slate-500">
                            Belum ada menu yang aktif. Tambahkan menu terlebih dahulu.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Sisi Kanan: Panel Pesanan Aktif (Keranjang & Pembayaran Kasir) -->
            <div>
                <div class="sticky top-20 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Transaksi Berjalan</p>
                            <h2 class="text-lg font-black text-slate-900">Pesanan Aktif</h2>
                        </div>
                        <button type="button" onclick="clearCart()" class="text-xs font-bold text-red-600 hover:underline">Kosongkan</button>
                    </div>

                    <!-- Pilihan Tipe Pesanan (Dine-in / Takeaway) -->
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button" id="btnDineIn" onclick="setOrderType('dine_in')" class="rounded-xl border border-amber-500 bg-amber-50 py-2 text-xs font-bold text-amber-900 transition">
                            🍽️ Makan di Tempat
                        </button>
                        <button type="button" id="btnTakeaway" onclick="setOrderType('takeaway')" class="rounded-xl border border-slate-200 bg-white py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                            🥡 Bawa Pulang
                        </button>
                    </div>

                    <!-- Input Nama Pelanggan / Catatan Meja -->
                    <div class="mt-3">
                        <input type="text" id="customerNameInput" placeholder="Nama Pelanggan / No. Meja (opsional)" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-amber-500 focus:outline-none">
                    </div>

                    <!-- Daftar Item Dalam Pesanan -->
                    <div id="cartItemsContainer" class="mt-4 max-h-[260px] space-y-2.5 overflow-y-auto divide-y divide-slate-100 pr-1">
                        <p id="emptyCartNotice" class="py-10 text-center text-xs text-slate-400">Pilih menu di sebelah kiri untuk memulai pesanan.</p>
                    </div>

                    <!-- Ringkasan Perhitungan -->
                    <div class="mt-4 border-t border-slate-100 pt-3 space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span id="subtotalLabel" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak (0%):</span>
                            <span>Rp 0</span>
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 pt-1 border-t border-dashed border-slate-200">
                            <span>Total Bayar:</span>
                            <span id="totalLabel" class="text-amber-700">Rp 0</span>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-amber-500 bg-amber-50 p-2.5 text-xs font-bold text-amber-900">
                                <input type="radio" name="payMethod" value="cash" checked onchange="togglePayMethod('cash')">
                                💵 Tunai (Cash)
                            </label>
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 p-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                <input type="radio" name="payMethod" value="qris" onchange="togglePayMethod('qris')">
                                📱 QRIS Warung
                            </label>
                        </div>
                    </div>

                    <!-- Area Input Pembayaran Tunai & Kembalian -->
                    <div id="cashPayArea" class="mt-3 rounded-2xl bg-slate-50 p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Nominal Diterima:</span>
                            <input 
                                type="number" 
                                id="receivedCashInput" 
                                placeholder="0" 
                                min="0" 
                                class="w-32 rounded-lg border border-slate-300 bg-white px-2 py-1 text-right text-xs font-bold text-slate-900 focus:border-amber-500 focus:outline-none"
                                oninput="calculateChange()"
                            >
                        </div>

                        <!-- Tombol Cepat Nominal Uang Pas / Pecahan -->
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <button type="button" onclick="setQuickCash('pas')" class="rounded-lg bg-white border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-amber-100">Uang Pas</button>
                            <button type="button" onclick="setQuickCash(20000)" class="rounded-lg bg-white border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-amber-100">20k</button>
                            <button type="button" onclick="setQuickCash(50000)" class="rounded-lg bg-white border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-amber-100">50k</button>
                            <button type="button" onclick="setQuickCash(100000)" class="rounded-lg bg-white border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-amber-100">100k</button>
                        </div>

                        <div class="mt-2.5 flex justify-between border-t border-slate-200 pt-2 text-xs">
                            <span class="font-semibold text-slate-600">Kembalian:</span>
                            <span id="changeLabel" class="font-black text-emerald-700">Rp 0</span>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Pembayaran -->
                    <button 
                        type="button" 
                        id="btnSubmitPos" 
                        onclick="processPosTransaction()" 
                        disabled 
                        class="mt-4 w-full rounded-2xl bg-amber-500 py-3.5 text-sm font-black text-slate-900 shadow-md shadow-amber-500/20 transition hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Proses & Cetak Struk
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: ANTREAN PESANAN ONLINE & DAPUR -->
        <!-- ========================================== -->
        <div id="queueView" class="mt-6 hidden">
            <!-- Summary Status Antrean -->
            <div class="grid grid-cols-3 gap-2 sm:gap-4">
                @foreach ([['new', 'Pesanan Baru', 'bg-amber-100 text-amber-800'], ['preparing', 'Sedang Disiapkan', 'bg-blue-100 text-blue-800'], ['ready', 'Siap Diambil', 'bg-emerald-100 text-emerald-800']] as [$status, $label, $style])
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                        <p class="text-xs text-slate-500 sm:text-sm">{{ $label }}</p>
                        <p class="mt-2 text-xl font-black text-slate-900 sm:text-2xl">{{ $orders->where('kitchen_status', $status)->count() }}</p>
                    </div>
                @endforeach
            </div>

            <!-- List Pesanan Aktif -->
            <div class="mt-6 space-y-4">
                @forelse ($orders as $order)
                    @php
                        $kitchenLabels = ['new' => 'Pesanan Baru', 'preparing' => 'Sedang Disiapkan', 'ready' => 'Siap Diambil', 'completed' => 'Selesai'];
                        $kitchenStyles = ['new' => 'bg-amber-100 text-amber-800', 'preparing' => 'bg-blue-100 text-blue-800', 'ready' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-slate-100 text-slate-700'];
                    @endphp
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center md:px-7">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-black text-slate-900">Pesanan #{{ $order->id }}</h2>
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $kitchenStyles[$order->kitchen_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $kitchenLabels[$order->kitchen_status] ?? $order->kitchen_status }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">{{ $order->payment_status === 'paid' ? 'Lunas' : 'Menunggu pembayaran' }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ $order->customer_name ?: 'Pelanggan' }} · {{ $order->customer_phone ?: 'Tanpa nomor' }} · {{ $order->created_at->format('H:i') }} WIB</p>
                            </div>
                            <div class="text-left sm:text-right flex items-center justify-between sm:block gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total</p>
                                    <p class="text-2xl font-black text-amber-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->payment_method === 'qris' ? 'QRIS / Digital' : 'Bayar di warung' }}</p>
                                </div>
                                <a href="{{ route('pos.orders.receipt', $order) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-bold text-slate-700 hover:border-amber-400">
                                    🖨️ Struk
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:gap-6 sm:p-5 md:grid-cols-[1fr_0.8fr] md:p-7">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Item Pesanan</h3>
                                <div class="mt-3 divide-y divide-slate-100">
                                    @foreach ($order->details as $detail)
                                        <div class="flex justify-between gap-4 py-2 text-sm">
                                            <span class="font-semibold text-slate-800">{{ $detail->menu->name }} <span class="text-slate-500">× {{ $detail->quantity }}</span></span>
                                            <span class="font-bold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-4 rounded-2xl bg-slate-50 p-4">
                                @if ($order->payment_method === 'qris')
                                    @if ($order->receipt_image)
                                        <a href="{{ route('staff.orders.proof', $order) }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-blue-800 hover:bg-blue-100">
                                            Lihat bukti QRIS
                                        </a>
                                    @else
                                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Menunggu pelanggan mengunggah bukti QRIS.</p>
                                    @endif
                                @endif

                                <form method="POST" action="{{ route('pos.orders.kitchen', $order) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="kitchen-status-{{ $order->id }}" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Status Dapur</label>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <select id="kitchen-status-{{ $order->id }}" name="kitchen_status" class="min-h-11 min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800">
                                            <option value="new" @selected($order->kitchen_status === 'new')>Pesanan baru</option>
                                            <option value="preparing" @selected($order->kitchen_status === 'preparing')>Sedang disiapkan</option>
                                            <option value="ready" @selected($order->kitchen_status === 'ready')>Siap diambil</option>
                                            <option value="completed" @selected($order->kitchen_status === 'completed')>Selesai</option>
                                        </select>
                                        <button class="min-h-11 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700">Simpan</button>
                                    </div>
                                </form>

                                @if ($order->status !== 'paid' && ($order->payment_method !== 'qris' || $order->receipt_image))
                                    <form method="POST" action="{{ route('pos.orders.payment', $order) }}" onsubmit="return confirm('Konfirmasi pembayaran pesanan #{{ $order->id }}? Stok akan dipotong otomatis setelah dikonfirmasi.');">
                                        @csrf
                                        <button class="min-h-12 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-500">
                                            Konfirmasi pembayaran & potong stok
                                        </button>
                                    </form>
                                @elseif ($order->status === 'paid')
                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-center text-xs font-bold text-emerald-800">
                                        ✓ Pembayaran Terkonfirmasi & Stok Sudah Terpotong
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                        <div class="text-5xl">🍛</div>
                        <h2 class="mt-4 text-xl font-black text-slate-900">Belum Ada Pesanan Antrean</h2>
                        <p class="mt-2 text-sm text-slate-600">Pesanan dari website pelanggan akan muncul di sini secara otomatis.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<!-- ========================================== -->
<!-- MODAL SUKSES TRANSAKSI & CETAK STRUK -->
<!-- ========================================== -->
<div id="successModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-600">
            ✓
        </div>
        <h3 class="mt-4 text-xl font-black text-slate-900">Transaksi Selesai!</h3>
        <p id="successOrderId" class="mt-1 text-sm text-slate-500">Order #</p>
        <div class="mt-4 rounded-2xl bg-slate-50 p-3 text-sm">
            <div class="flex justify-between text-slate-600">
                <span>Total Belanja:</span>
                <span id="modalTotal" class="font-bold text-slate-900">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-600 mt-1">
                <span>Kembalian:</span>
                <span id="modalChange" class="font-black text-emerald-700">Rp 0</span>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-2.5">
            <a id="modalPrintBtn" href="#" target="_blank" class="rounded-2xl bg-amber-500 py-3 text-sm font-black text-slate-900 hover:bg-amber-400">
                🖨️ Cetak Struk Thermal
            </a>
            <button type="button" onclick="closeSuccessModal()" class="rounded-2xl border border-slate-200 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                Tutup & Transaksi Baru
            </button>
        </div>
    </div>
</div>

<script>
    // State Keranjang Kasir
    let cart = [];
    let orderType = 'dine_in';
    let currentCategory = 'all';

    function switchTab(mode) {
        const posView = document.getElementById('posView');
        const queueView = document.getElementById('queueView');
        const tabPosBtn = document.getElementById('tabPosBtn');
        const tabQueueBtn = document.getElementById('tabQueueBtn');

        if (mode === 'pos') {
            posView.classList.remove('hidden');
            queueView.classList.add('hidden');
            tabPosBtn.className = 'inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 text-xs font-bold transition shadow-sm bg-slate-900 text-white sm:text-sm';
            tabQueueBtn.className = 'inline-flex min-h-11 items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2 text-xs font-bold text-slate-800 transition hover:border-amber-400 sm:text-sm';
        } else {
            posView.classList.add('hidden');
            queueView.classList.remove('hidden');
            tabQueueBtn.className = 'inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 text-xs font-bold transition shadow-sm bg-slate-900 text-white sm:text-sm';
            tabPosBtn.className = 'inline-flex min-h-11 items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2 text-xs font-bold text-slate-800 transition hover:border-amber-400 sm:text-sm';
        }
    }

    function setOrderType(type) {
        orderType = type;
        const btnDineIn = document.getElementById('btnDineIn');
        const btnTakeaway = document.getElementById('btnTakeaway');

        if (type === 'dine_in') {
            btnDineIn.className = 'rounded-xl border border-amber-500 bg-amber-50 py-2 text-xs font-bold text-amber-900 transition';
            btnTakeaway.className = 'rounded-xl border border-slate-200 bg-white py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50';
        } else {
            btnTakeaway.className = 'rounded-xl border border-amber-500 bg-amber-50 py-2 text-xs font-bold text-amber-900 transition';
            btnDineIn.className = 'rounded-xl border border-slate-200 bg-white py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50';
        }
    }

    function filterCategory(catId, btn) {
        currentCategory = catId;
        document.querySelectorAll('.category-btn').forEach(b => {
            b.className = 'category-btn rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-200';
        });
        btn.className = 'category-btn rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition';
        filterMenus();
    }

    function filterMenus() {
        const query = document.getElementById('menuSearch').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.menu-card');

        cards.forEach(card => {
            const name = card.dataset.name;
            const cat = card.dataset.category;
            const matchQuery = name.includes(query);
            const matchCategory = (currentCategory === 'all' || cat === currentCategory);

            if (matchQuery && matchCategory) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function addToCart(menuId, menuName, price, maxStock) {
        const existing = cart.find(item => item.id === menuId);
        if (existing) {
            if (existing.qty < maxStock) {
                existing.qty += 1;
            } else {
                alert(`Maksimal stok tersedia untuk ${menuName} adalah ${maxStock} porsi.`);
                return;
            }
        } else {
            cart.push({ id: menuId, name: menuName, price: price, qty: 1, maxStock: maxStock });
        }
        renderCart();
    }

    function updateCartQty(menuId, delta) {
        const item = cart.find(i => i.id === menuId);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== menuId);
        } else if (item.qty > item.maxStock) {
            item.qty = item.maxStock;
            alert(`Stok hanya tersisa ${item.maxStock}.`);
        }
        renderCart();
    }

    function removeCartItem(menuId) {
        cart = cart.filter(i => i.id !== menuId);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const emptyNotice = document.getElementById('emptyCartNotice');
        const subtotalLabel = document.getElementById('subtotalLabel');
        const totalLabel = document.getElementById('totalLabel');
        const submitBtn = document.getElementById('btnSubmitPos');

        if (cart.length === 0) {
            container.innerHTML = '<p id="emptyCartNotice" class="py-10 text-center text-xs text-slate-400">Pilih menu di sebelah kiri untuk memulai pesanan.</p>';
            subtotalLabel.textContent = 'Rp 0';
            totalLabel.textContent = 'Rp 0';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Proses & Cetak Struk';
            calculateChange();
            return;
        }

        let total = 0;
        let html = '';

        cart.forEach(item => {
            const itemSubtotal = item.price * item.qty;
            total += itemSubtotal;

            html += `
                <div class="flex items-center justify-between py-2 text-xs">
                    <div class="min-w-0 flex-1 pr-2">
                        <p class="font-bold text-slate-900 truncate">${item.name}</p>
                        <p class="text-slate-500">Rp ${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="updateCartQty(${item.id}, -1)" class="h-6 w-6 rounded-lg bg-slate-200 text-slate-700 font-bold hover:bg-slate-300">-</button>
                        <span class="w-6 text-center font-bold text-slate-900">${item.qty}</span>
                        <button type="button" onclick="updateCartQty(${item.id}, 1)" class="h-6 w-6 rounded-lg bg-slate-200 text-slate-700 font-bold hover:bg-slate-300">+</button>
                    </div>
                    <div class="text-right shrink-0 w-20 pl-2">
                        <p class="font-bold text-slate-900">Rp ${itemSubtotal.toLocaleString('id-ID')}</p>
                        <button type="button" onclick="removeCartItem(${item.id})" class="text-[10px] text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        subtotalLabel.textContent = `Rp ${total.toLocaleString('id-ID')}`;
        totalLabel.textContent = `Rp ${total.toLocaleString('id-ID')}`;
        submitBtn.disabled = false;
        submitBtn.textContent = `Bayar Rp ${total.toLocaleString('id-ID')} & Cetak`;

        calculateChange();
    }

    function togglePayMethod(method) {
        const cashArea = document.getElementById('cashPayArea');
        if (method === 'cash') {
            cashArea.classList.remove('hidden');
        } else {
            cashArea.classList.add('hidden');
        }
        calculateChange();
    }

    function setQuickCash(amount) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const input = document.getElementById('receivedCashInput');
        if (amount === 'pas') {
            input.value = total;
        } else {
            input.value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const inputVal = Number(document.getElementById('receivedCashInput').value || 0);
        const changeLabel = document.getElementById('changeLabel');

        const change = Math.max(0, inputVal - total);
        changeLabel.textContent = `Rp ${change.toLocaleString('id-ID')}`;
    }

    async function processPosTransaction() {
        if (cart.length === 0) return;

        const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const method = document.querySelector('input[name="payMethod"]:checked').value;
        const receivedCash = Number(document.getElementById('receivedCashInput').value || 0);
        const custName = document.getElementById('customerNameInput').value;

        if (method === 'cash' && receivedCash < total) {
            alert(`Nominal tunai diterima (Rp ${receivedCash.toLocaleString('id-ID')}) kurang dari total belanja (Rp ${total.toLocaleString('id-ID')}).`);
            return;
        }

        const submitBtn = document.getElementById('btnSubmitPos');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses Transaksi...';

        try {
            const response = await fetch(@json(route('pos.transactions.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    customer_name: custName,
                    order_type: orderType,
                    payment_method: method,
                    received_amount: method === 'cash' ? receivedCash : total,
                    items: cart.map(item => ({ menu_id: item.id, quantity: item.qty })),
                })
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Gagal memproses transaksi.'));
                submitBtn.disabled = false;
                renderCart();
                return;
            }

            // Sukses
            showSuccessModal(data.transaction, data.receipt_url);
            clearCart();
            document.getElementById('customerNameInput').value = '';
            document.getElementById('receivedCashInput').value = '';
        } catch (err) {
            alert('Terjadi kesalahan jaringan atau server. Silakan coba lagi.');
            submitBtn.disabled = false;
            renderCart();
        }
    }

    function showSuccessModal(transaction, receiptUrl) {
        document.getElementById('successOrderId').textContent = `Order #${transaction.id} · ${transaction.customer_name}`;
        document.getElementById('modalTotal').textContent = `Rp ${Number(transaction.total_amount).toLocaleString('id-ID')}`;
        document.getElementById('modalChange').textContent = `Rp ${Number(transaction.change_amount).toLocaleString('id-ID')}`;
        document.getElementById('modalPrintBtn').href = receiptUrl;
        
        const modal = document.getElementById('successModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        window.location.reload();
    }

    // Auto-refresh antrean jika aktif di tab antrean
    window.setInterval(() => {
        const queueView = document.getElementById('queueView');
        if (queueView && !queueView.classList.contains('hidden')) {
            const focusedElement = document.activeElement;
            const cashierIsEditing = focusedElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(focusedElement.tagName);
            if (document.visibilityState === 'visible' && !cashierIsEditing) {
                window.location.reload();
            }
        }
    }, 15000);
</script>
@endsection
