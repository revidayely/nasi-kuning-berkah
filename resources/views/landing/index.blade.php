@extends('layouts.app')

@section('title', 'Warung Nasi Kuning Berkah - Autentik, Cita Rasa Berkah')

@section('content')
    @php
        $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.warung.whatsapp', '6287848361913'));
        $qrisImage = config('services.warung.qris_image');
    @endphp

    <!-- 1. HERO SECTION -->
    <section class="relative overflow-hidden bg-[#f9f4eb] py-10 lg:py-16">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(239,68,68,0.1),transparent_30%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
            <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <!-- Teks Hero -->
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100/80 px-3.5 py-1 text-xs font-black uppercase tracking-[0.25em] text-amber-800">
                        <span>🍚</span> Nasi Kuning Autentik, Cita Rasa Berkah
                    </div>
                    <h1 class="mt-5 text-4xl font-black leading-[1.15] text-slate-900 sm:text-5xl lg:text-6xl">
                        Nasi Kuning Autentik, <span class="text-amber-600">Cita Rasa Berkah</span>
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-700 sm:text-lg">
                        Nasi kuning harum, lauk konsisten, dan sambal yang bikin rindu. Panas mengepul dari fajar, disajikan dengan sepenuh hati untuk mengawali harimu.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="#menu" class="rounded-full bg-amber-500 px-7 py-3.5 text-sm font-black text-slate-900 shadow-lg shadow-amber-500/30 transition hover:bg-amber-400">
                            Lihat Menu ↓
                        </a>
                        <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Warung%20Nasi%20Kuning%20Berkah,%20saya%20ingin%20pesan" target="_blank" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition hover:border-emerald-500 hover:text-emerald-700">
                            <span>💬 WhatsApp Kami</span>
                        </a>
                    </div>

                    <!-- 3 Nilai Utama -->
                    <div class="mt-10 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-600">01 Resep</p>
                            <p class="mt-1 font-bold text-slate-900 text-sm">Turun-Temurun</p>
                            <p class="mt-0.5 text-xs text-slate-500">Rempah bumbu asli keluarga sejak 2008.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-600">02 Dibuat Pagi</p>
                            <p class="mt-1 font-bold text-slate-900 text-sm">Fresh Pukul 05.00</p>
                            <p class="mt-0.5 text-xs text-slate-500">Hangat mengepul setiap pagi hari.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-600">03 Kotakan</p>
                            <p class="mt-1 font-bold text-slate-900 text-sm">Pesan Selamatan</p>
                            <p class="mt-0.5 text-xs text-slate-500">Siap bento & nasi box acara syukuran.</p>
                        </div>
                    </div>
                </div>

                <!-- Gambar Hero Banner Promo (Paket Komplit Rp25.000) -->
                <div class="relative">
                    <div class="relative overflow-hidden rounded-[2.5rem] border-4 border-white bg-slate-900 p-2 shadow-[0_25px_70px_rgba(245,158,11,0.18)]">
                        <img 
                            src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80" 
                            alt="Nasi Kuning Paket Komplit Berkah" 
                            class="h-[420px] w-full rounded-[2rem] object-cover sm:h-[460px]"
                        >
                        <!-- Floating Badge Card -->
                        <div class="absolute bottom-6 left-6 right-6 rounded-2xl border border-white/40 bg-white/95 p-4 shadow-xl backdrop-blur-md sm:bottom-8 sm:left-8 sm:right-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="rounded-md bg-amber-100 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest text-amber-800">Menu Andalan</span>
                                    <h3 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">Paket Komplit Berkah</h3>
                                    <p class="text-xs text-slate-600">Ayam goreng, telur balado, orek tempe, perkedel & sambal</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-400 line-through">Rp 28.000</p>
                                    <p class="text-xl font-black text-amber-600 sm:text-2xl">Rp 25.000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. MENU SHOWCASE SECTION (Sepiring nikmat, banyak pilihan) -->
    <section id="menu" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Menu Warung</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900 sm:text-4xl">Sepiring nikmat, banyak pilihan.</h2>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Pilih paket lezat atau lengkapi piringmu dengan pilihan lauk favorit keluarga.</p>
            </div>
            <!-- Category Filter Pills -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="filterLandingCategory('all', this)" class="landing-cat-btn rounded-full bg-amber-500 px-4 py-2 text-xs font-bold text-slate-900 transition">Semua Menu</button>
                @foreach ($categories as $cat)
                    <button type="button" onclick="filterLandingCategory('{{ $cat->id }}', this)" class="landing-cat-btn rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-amber-400">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>

        <!-- Grid Menu Cards -->
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
            @foreach ($menus as $menu)
                @php
                    $stock = $menu->dailyStocks->first()?->quantity ?? 0;
                    $isAvailable = $stock > 0;
                @endphp
                <article 
                    class="landing-menu-card group flex flex-col justify-between overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                    data-cat="{{ $menu->category_id }}"
                >
                    <div class="relative">
                        <img 
                            src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80' }}" 
                            alt="{{ $menu->name }}" 
                            class="h-56 w-full object-cover transition duration-300 group-hover:scale-105"
                        >
                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold text-slate-800 shadow-sm backdrop-blur-sm">
                            {{ $menu->category->name ?? 'Menu' }}
                        </span>
                        <span class="absolute right-4 top-4 rounded-full px-3 py-1 text-[11px] font-black {{ $isAvailable ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }} shadow-sm">
                            {{ $isAvailable ? "Stok: {$stock}" : 'Habis Hari Ini' }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-xl font-black text-slate-900">{{ $menu->name }}</h3>
                                <p class="whitespace-nowrap text-lg font-black text-amber-600">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $menu->description }}</p>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-xs font-semibold text-slate-500">
                                {{ $isAvailable ? 'Siap disajikan' : 'Bisa dipesan besok' }}
                            </span>
                            <button
                                type="button"
                                class="add-to-cart-btn inline-flex items-center gap-1.5 rounded-full {{ $isAvailable ? 'bg-slate-900 text-white hover:bg-amber-500 hover:text-slate-900' : 'cursor-not-allowed bg-slate-200 text-slate-400' }} px-5 py-2.5 text-xs font-bold transition"
                                data-menu-id="{{ $menu->id }}"
                                data-menu-name="{{ $menu->name }}"
                                data-menu-price="{{ $menu->price }}"
                                {{ $isAvailable ? '' : 'disabled' }}
                            >
                                <span>+</span> {{ $isAvailable ? 'Pesan Menu' : 'Habis' }}
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <!-- 3. STORY SECTION (Sederhana sajiannya, Hangat kebersamaannya) -->
    <section class="bg-[#f2ece1] py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="grid items-center gap-10 md:grid-cols-2">
                <div class="relative overflow-hidden rounded-3xl shadow-xl">
                    <img 
                        src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80" 
                        alt="Suasana Dapur Warung Nasi Kuning Berkah" 
                        class="h-[380px] w-full object-cover"
                    >
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-700">Cerita Rasa Kami</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">
                        Sederhana sajiannya, Hangat kebersamaannya.
                    </h2>
                    <p class="mt-4 text-base leading-relaxed text-slate-700">
                        Bagi kami, nasi kuning bukan sekadar hidangan pengganjal lapar. Di dalamnya ada harum kunyit dan santan kelapa segar, rempah lauk yang mengingatkan masakan rumah, serta gurih serundeng yang menemani pagi penuh semangat.
                    </p>
                    <p class="mt-3 text-base leading-relaxed text-slate-700">
                        Warung Nasi Kuning Berkah hadir dengan komitmen rasa yang tidak pernah berubah sejak hari pertama. Setiap bumbu diracik segar, setiap porsi disiapkan dengan doa agar menjadi berkah bagi siapapun yang menikmatinya.
                    </p>
                    <div class="mt-6 flex items-center gap-4">
                        <span class="rounded-2xl bg-white px-4 py-3 text-xs font-bold text-slate-800 shadow-sm">
                            ⭐ 4.9 dari 500+ Ulasan Tamu
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. GALLERY SECTION (Intip kehangatan di warung) -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Suasana Warung</p>
            <h2 class="mt-1 text-3xl font-black text-slate-900 sm:text-4xl">Intip kehangatan di warung kami.</h2>
            <p class="mt-2 text-sm text-slate-600">Dari meja dapur subuh hingga senyum tamu yang menikmati suapan pertama.</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            <div class="group relative overflow-hidden rounded-3xl shadow-sm">
                <img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80" alt="Sepiring menu komplit" class="h-64 w-full object-cover transition duration-300 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end p-5">
                    <p class="text-sm font-bold text-white">Sepiring menu, santap lezat</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-3xl shadow-sm">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80" alt="Lauk tumisan sedap" class="h-64 w-full object-cover transition duration-300 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end p-5">
                    <p class="text-sm font-bold text-white">Lauk tumisan, sedap dimasak</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-3xl shadow-sm">
                <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80" alt="Tempat singgah yang hangat" class="h-64 w-full object-cover transition duration-300 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end p-5">
                    <p class="text-sm font-bold text-white">Tempat singgah yang hangat</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TESTIMONIAL SECTION (Yang bikin ingin kembali) -->
    <section class="bg-[#f9f4eb] py-16 border-t border-amber-200/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Kata Pelanggan</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900 sm:text-4xl">Yang bikin ingin kembali.</h2>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex text-amber-500 text-sm">★★★★★</div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-700">
                        "Tekstur nasinya pulen, bumbu kuningnya wangi meresap. Paket komplitnya juara banget buat sarapan sebelum mulai kerja."
                    </p>
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="font-bold text-slate-900 text-sm">Rian Pratama</p>
                        <p class="text-xs text-slate-500">Pelanggan setia sejak 2019</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex text-amber-500 text-sm">★★★★★</div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-700">
                        "Sambalnya pas mantap, perkedelnya lembut gurih. Rasanya konsisten dan selalu bikin kangen masakan ibu di rumah."
                    </p>
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="font-bold text-slate-900 text-sm">Amanda Putri</p>
                        <p class="text-xs text-slate-500">Karyawan swasta</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex text-amber-500 text-sm">★★★★★</div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-700">
                        "Porsinya mantap dan lauknya lengkap. Langganan pesan nasi kotak untuk selamatan keluarga di akhir pekan, semuanya puas!"
                    </p>
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="font-bold text-slate-900 text-sm">Bima Wicaksono</p>
                        <p class="text-xs text-slate-500">Warga Bandung</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. LOCATION & HOURS (Ada tempat untukmu di meja kami) -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
        <div class="grid gap-10 rounded-[2.5rem] border border-amber-200 bg-white p-6 shadow-sm md:grid-cols-2 md:p-10">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Lokasi & Jam Buka</p>
                <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Ada tempat untukmu di meja kami.</h2>
                <p class="mt-4 text-sm text-slate-600">
                    Datang langsung dan nikmati hangatnya nasi kuning segar dari dandang, atau pesan terlebih dahulu untuk diambil tepat waktu.
                </p>

                <div class="mt-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">📍</span>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Warung Nasi Kuning Berkah</p>
                            <p class="text-xs text-slate-600">Jl. Nasi Kuning Berkah No. 12, Bandung (Dekat Lapangan Gasibu)</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="text-xl">⏰</span>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Jam Operasional</p>
                            <p class="text-xs text-slate-600">Buka Setiap Hari: 05.30 - 13.00 WIB (Hingga habis)</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="text-xl">💳</span>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">Metode Pembayaran</p>
                            <p class="text-xs text-slate-600">Tunai (Cash) di kasir atau QRIS / E-Wallet digital</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Warung%20Nasi%20Kuning%20Berkah,%20boleh%20minta%20share%20location?" target="_blank" class="rounded-full bg-slate-900 px-6 py-3 text-xs font-bold text-white hover:bg-slate-800 transition">
                        💬 Tanyakan Lokasi via WhatsApp
                    </a>
                    <a href="https://maps.google.com" target="_blank" class="rounded-full border border-slate-300 px-6 py-3 text-xs font-bold text-slate-800 hover:border-amber-400 transition">
                        Buka Google Maps ↗
                    </a>
                </div>
            </div>

            <!-- Map Mockup Container -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 flex items-center justify-center min-h-[300px]">
                <div class="text-center p-6">
                    <div class="text-4xl mb-2">🗺️</div>
                    <p class="font-bold text-slate-900">Peta Lokasi Warung</p>
                    <p class="text-xs text-slate-500 mt-1">Jl. Nasi Kuning Berkah No. 12, Bandung</p>
                    <span class="mt-4 inline-block rounded-full bg-amber-500 px-4 py-1.5 text-xs font-bold text-slate-900">
                        Titik Warung Nasi Kuning Berkah
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. BOTTOM BANNER & STEPS (Hari yang baik dimulai dari sepiring berkah) -->
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6">
        <div class="rounded-[2.5rem] bg-gradient-to-r from-amber-500 to-amber-400 p-8 text-slate-900 sm:p-12 shadow-xl shadow-amber-500/20">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center">
                <div>
                    <h2 class="text-3xl font-black sm:text-4xl">Hari yang baik dimulai dari sepiring berkah.</h2>
                    <p class="mt-2 text-sm text-slate-800 sm:text-base font-medium">Pesan sarapan favoritmu sekarang, ambil hangat tanpa antre.</p>
                </div>
                <a href="#menu" class="self-start rounded-full bg-slate-900 px-8 py-4 text-sm font-black text-white shadow-lg transition hover:bg-slate-800 md:self-center">
                    Pesan Sekarang →
                </a>
            </div>

            <div class="mt-10 grid gap-6 border-t border-amber-600/30 pt-8 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-800">01 Pilih Menu</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Pilih nasi kuning dan lauk kesukaanmu dari daftar menu.</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-800">02 Konfirmasi Pesanan</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Pilih bayar di warung atau via QRIS dan kirim bukti transaksi.</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-800">03 Nikmati Kehangatannya</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Pesanan disiapkan rapi, siap dinikmati di tempat atau dibawa pulang.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Notification Toast -->
    <script>
        const cartKey = 'warung-naskun-cart';
        const notification = document.createElement('div');
        notification.className = 'pointer-events-none fixed left-1/2 top-20 z-50 -translate-x-1/2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white opacity-0 shadow-xl transition';
        notification.setAttribute('role', 'status');
        document.body.appendChild(notification);
        let notificationTimer;

        document.querySelectorAll('.add-to-cart-btn').forEach((button) => {
            button.addEventListener('click', () => {
                const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
                const menuId = Number(button.dataset.menuId);
                const item = cart.find(entry => entry.id === menuId);

                if (item) {
                    item.quantity += 1;
                } else {
                    cart.push({
                        id: menuId,
                        name: button.dataset.menuName,
                        price: Number(button.dataset.menuPrice),
                        quantity: 1,
                    });
                }

                localStorage.setItem(cartKey, JSON.stringify(cart));
                notification.textContent = `✓ ${button.dataset.menuName} ditambahkan ke keranjang`;
                notification.classList.remove('opacity-0');
                clearTimeout(notificationTimer);
                notificationTimer = setTimeout(() => notification.classList.add('opacity-0'), 2000);
                updateCartNav();
            });
        });

        function updateCartNav() {
            const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);
            const navLink = document.getElementById('navCartLink');
            if (navLink) {
                navLink.textContent = cartCount > 0 ? `Keranjang (${cartCount})` : 'Keranjang';
            }
        }

        function filterLandingCategory(catId, btn) {
            document.querySelectorAll('.landing-cat-btn').forEach(b => {
                b.className = 'landing-cat-btn rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-amber-400';
            });
            btn.className = 'landing-cat-btn rounded-full bg-amber-500 px-4 py-2 text-xs font-bold text-slate-900 transition';

            const cards = document.querySelectorAll('.landing-menu-card');
            cards.forEach(c => {
                if (catId === 'all' || c.dataset.cat === catId) {
                    c.style.display = 'flex';
                } else {
                    c.style.display = 'none';
                }
            });
        }

        updateCartNav();
    </script>
@endsection
