@extends('layouts.app')

@section('title', 'Keranjang Pesanan - Warung Nasi Kuning Berkah')

@section('content')
<section class="min-h-[75vh] bg-[#f9f4eb] px-4 py-8 sm:px-6 sm:py-12">
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('home') }}#menu" class="text-xs sm:text-sm font-bold text-amber-700 hover:text-amber-900">← Kembali pilih menu</a>
        <div class="mt-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Pesanan Anda</p>
                <h1 class="mt-1 text-3xl font-black text-slate-900 sm:text-4xl">Keranjang Belanja</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-600">Periksa daftar pesanan sebelum checkout.</p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div id="empty-cart" class="hidden rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                    <div class="text-5xl">🧺</div>
                    <h2 class="mt-4 text-xl font-black text-slate-900">Keranjang masih kosong</h2>
                    <p class="mt-2 text-sm text-slate-600">Yuk pilih paket nasi kuning dan aneka lauk favoritmu.</p>
                    <a href="{{ route('home') }}#menu" class="mt-5 inline-flex rounded-full bg-amber-500 px-6 py-3 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/20 hover:bg-amber-400">Pilih Menu Sekarang</a>
                </div>
                <div id="cart-items" class="space-y-4"></div>
            </div>

            <aside class="h-fit rounded-3xl bg-slate-900 p-6 text-white shadow-xl shadow-slate-900/10">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-400">Ringkasan Pesanan</p>
                <div class="mt-5 flex justify-between text-sm text-slate-300">
                    <span>Jumlah Porsi</span>
                    <span id="items-count" class="font-bold text-white">0</span>
                </div>
                <div class="mt-4 flex justify-between border-t border-slate-700 pt-4 text-sm text-slate-300">
                    <span>Total Pembayaran</span>
                    <span id="cart-total" class="text-2xl font-black text-amber-400">Rp 0</span>
                </div>
                <button id="open-checkout" type="button" class="mt-6 w-full rounded-full bg-amber-500 px-5 py-3.5 font-black text-slate-900 transition hover:bg-amber-400 shadow-lg shadow-amber-500/20">Lanjut ke Pembayaran →</button>
                <p class="mt-4 text-xs leading-5 text-slate-400">
                    💡 Tersedia pembayaran via <strong>QRIS (Semua E-Wallet & Bank)</strong> atau <strong>Bayar Langsung di Warung</strong>.
                </p>
            </aside>
        </div>
    </div>
</section>

<!-- Modal Checkout -->
<div id="checkout-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
    <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-600">Checkout Pesanan</p>
                <h2 class="mt-1 text-2xl font-black text-slate-900">Informasi Pemesan</h2>
            </div>
            <button id="close-checkout" type="button" class="rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200">✕ Tutup</button>
        </div>

        <form id="checkout-form" method="POST" action="{{ route('orders.store') }}" class="mt-6 space-y-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="customer_name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Nama Pemesan</label>
                    <input id="customer_name" name="customer_name" required placeholder="Cth: Budi Santoso" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-amber-500 focus:bg-white">
                </div>
                <div>
                    <label for="customer_phone" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Nomor WhatsApp Aktif</label>
                    <input id="customer_phone" name="customer_phone" type="tel" required placeholder="081234567890" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-amber-500 focus:bg-white">
                </div>
            </div>

            <fieldset>
                <legend class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700">Pilih Metode Pembayaran</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-2xl border-2 border-amber-500 bg-amber-50/80 p-4 transition">
                        <input type="radio" name="payment_method" value="qris" checked class="mt-1 accent-amber-600">
                        <span>
                            <strong class="block text-slate-900 text-sm font-black">📱 QRIS / E-Wallet</strong>
                            <small class="text-xs text-slate-600 mt-0.5 block">Scan QRIS & wajib kirim bukti ke WhatsApp</small>
                        </span>
                    </label>

                    <label class="flex cursor-pointer gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-amber-400">
                        <input type="radio" name="payment_method" value="pickup" class="mt-1 accent-amber-600">
                        <span>
                            <strong class="block text-slate-900 text-sm font-bold">🏪 Bayar di Warung</strong>
                            <small class="text-xs text-slate-600 mt-0.5 block">Bayar tunai saat mengambil pesanan</small>
                        </span>
                    </label>
                </div>
            </fieldset>

            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200 text-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Item Dalam Pesanan</p>
                <div id="checkout-summary" class="space-y-1.5 text-slate-700 text-xs"></div>
                <div class="mt-3 flex justify-between border-t border-slate-200 pt-2 font-black text-slate-900 text-sm">
                    <span>Total Pembayaran</span>
                    <span id="checkout-total" class="text-amber-700 text-base">Rp 0</span>
                </div>
            </div>

            <input type="hidden" id="items-json" name="items" value="[]">
            <button type="submit" class="w-full rounded-full bg-emerald-600 py-3.5 text-sm font-black text-white transition hover:bg-emerald-500 shadow-lg shadow-emerald-600/20">
                Lanjut ke Detail Transaksi & Pembayaran →
            </button>
        </form>
    </div>
</div>

<div id="cart-toast" role="status" aria-live="polite" class="pointer-events-none fixed left-1/2 top-20 z-50 hidden -translate-x-1/2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-xl"></div>

<script>
    const cartKey = 'warung-naskun-cart';
    let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
    const cartItems = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const itemsCount = document.getElementById('items-count');
    const cartTotal = document.getElementById('cart-total');
    const checkoutSummary = document.getElementById('checkout-summary');
    const checkoutTotal = document.getElementById('checkout-total');
    const itemsJson = document.getElementById('items-json');
    const checkoutModal = document.getElementById('checkout-modal');
    const openCheckout = document.getElementById('open-checkout');
    const closeCheckout = document.getElementById('close-checkout');

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function renderCart() {
        if (!cart.length) {
            emptyCart.classList.remove('hidden');
            cartItems.innerHTML = '';
            itemsCount.textContent = '0';
            cartTotal.textContent = 'Rp 0';
            openCheckout.disabled = true;
            openCheckout.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        emptyCart.classList.add('hidden');
        openCheckout.disabled = false;
        openCheckout.classList.remove('opacity-50', 'cursor-not-allowed');

        let total = 0;
        let count = 0;
        let html = '';
        let summaryHtml = '';

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            count += item.quantity;

            html += `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 text-sm sm:text-base">${item.name}</p>
                        <p class="text-xs text-slate-500">${formatRupiah(item.price)} per porsi</p>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="changeQty(${index}, -1)" class="h-8 w-8 rounded-xl bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 transition">-</button>
                            <span class="w-8 text-center font-bold text-slate-900">${item.quantity}</span>
                            <button type="button" onclick="changeQty(${index}, 1)" class="h-8 w-8 rounded-xl bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 transition">+</button>
                        </div>
                        <p class="w-24 text-right font-black text-slate-900 text-sm">${formatRupiah(itemTotal)}</p>
                        <button type="button" onclick="removeItem(${index})" class="text-xs font-bold text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            `;

            summaryHtml += `
                <div class="flex justify-between">
                    <span>${item.name} × ${item.quantity}</span>
                    <span class="font-bold">${formatRupiah(itemTotal)}</span>
                </div>
            `;
        });

        cartItems.innerHTML = html;
        itemsCount.textContent = count;
        cartTotal.textContent = formatRupiah(total);
        checkoutSummary.innerHTML = summaryHtml;
        checkoutTotal.textContent = formatRupiah(total);
        itemsJson.value = JSON.stringify(cart.map(i => ({ menu_id: i.id, quantity: i.quantity })));
    }

    function changeQty(index, delta) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        localStorage.setItem(cartKey, JSON.stringify(cart));
        renderCart();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        localStorage.setItem(cartKey, JSON.stringify(cart));
        renderCart();
    }

    openCheckout.addEventListener('click', () => {
        if (!cart.length) return;
        checkoutModal.classList.remove('hidden');
        checkoutModal.classList.add('flex');
    });

    closeCheckout.addEventListener('click', () => {
        checkoutModal.classList.add('hidden');
        checkoutModal.classList.remove('flex');
    });

    renderCart();
</script>
@endsection
