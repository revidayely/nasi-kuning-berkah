@extends('layouts.app')

@section('title', 'Detail Transaksi & Pembayaran - Warung Nasi Kuning Berkah')

@section('content')
<script>
    // Kosongkan keranjang belanja lokal setelah order berhasil dibuat
    localStorage.removeItem('warung-naskun-cart');
</script>

@php
    $isQris = $transaction->payment_method === 'qris';
    $qrisImage = config('services.warung.qris_image');
    $qrisImage = filter_var($qrisImage, FILTER_VALIDATE_URL) ? $qrisImage : asset($qrisImage);
    $phone = preg_replace('/\D+/', '', (string) config('services.warung.whatsapp', '6287848361913'));
@endphp

<section class="min-h-[80vh] bg-[#f9f4eb] px-4 py-8 sm:px-6 sm:py-12">
    <div class="mx-auto max-w-4xl">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
            <!-- Header Konfirmasi -->
            <div class="border-b border-slate-100 bg-gradient-to-r {{ $isQris ? 'from-amber-500 to-amber-600' : 'from-slate-900 to-slate-800' }} p-6 text-white sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-block rounded-full bg-black/20 px-3.5 py-1 text-xs font-black uppercase tracking-wider backdrop-blur-sm">
                            {{ $isQris ? 'Metode: QRIS / E-Wallet' : 'Metode: Bayar di Warung' }}
                        </span>
                        <h1 class="mt-2 text-2xl font-black sm:text-3xl">Detail Transaksi Pesanan #{{ $transaction->id }}</h1>
                        <p class="mt-1 text-xs sm:text-sm text-white/90">Terima kasih, <strong>{{ $transaction->customer_name }}</strong>. Pesanan Anda telah berhasil tercatat di sistem.</p>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-white/80">Status Pembayaran</p>
                        <span class="mt-1 inline-block rounded-full bg-white px-3.5 py-1 text-xs font-black {{ $transaction->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-800' }} shadow-sm">
                            {{ $transaction->payment_status === 'paid' ? '✓ Lunas Terverifikasi' : ($transaction->payment_status === 'proof_submitted' ? '⏳ Bukti Terkirim (Menunggu Verifikasi)' : '⚠️ Menunggu Pembayaran') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <!-- Rincian Pesanan (Kiri) -->
                    <div class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Rincian Menu</h2>
                            <div class="mt-3 divide-y divide-slate-200">
                                @foreach ($transaction->details as $detail)
                                    <div class="flex justify-between gap-3 py-2.5 text-sm">
                                        <div>
                                            <span class="font-bold text-slate-900">{{ $detail->menu->name }}</span>
                                            <span class="text-slate-500">× {{ $detail->quantity }}</span>
                                        </div>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 border-t-2 border-slate-200 pt-3 flex justify-between items-center">
                                <span class="font-bold text-slate-700">Total Pembayaran:</span>
                                <span class="text-2xl font-black text-amber-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Info Pemesan -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600 space-y-1">
                            <p><strong>Nama:</strong> {{ $transaction->customer_name }}</p>
                            <p><strong>No. WhatsApp:</strong> {{ $transaction->customer_phone }}</p>
                            <p><strong>Waktu Order:</strong> {{ $transaction->created_at->format('d M Y, H:i') }} WIB</p>
                        </div>

                        <!-- Box Peringatan WhatsApp Wajib (QRIS) -->
                        @if ($isQris)
                            <div class="rounded-2xl border-2 border-amber-400 bg-amber-50 p-5">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">⚠️</span>
                                    <div>
                                        <h3 class="text-sm font-black text-amber-900 uppercase tracking-wide">Langkah Wajib Pembayaran QRIS:</h3>
                                        <ol class="mt-2 space-y-1.5 text-xs text-amber-900/90 list-decimal list-inside leading-relaxed font-medium">
                                            <li>Scan kode QRIS di sebelah kanan menggunakan GoPay, OVO, Dana, ShopeePay, BCA, atau m-banking apa saja.</li>
                                            <li>Transfer tepat sebesar <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>.</li>
                                            <li>Ambil tangkapan layar (screenshot) bukti transfer sukses.</li>
                                            <li><strong class="text-amber-950 font-bold">Wajib kirimkan Screenshot bukti QRIS ke WhatsApp kami</strong>. Kasir akan memverifikasi bukti secara manual sebelum stok dikurangi dan pesanan disiapkan.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Kolom Kanan: Tampilan QRIS & Tombol WhatsApp -->
                    <div>
                        @if ($isQris)
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                                <h2 class="text-base font-black text-slate-900">Bayar menggunakan QRIS</h2>
                                <p class="mt-1 text-xs text-slate-500">Mendukung semua aplikasi e-wallet & mobile banking</p>

                                <!-- Gambar QRIS -->
                                <div class="mt-4 inline-block overflow-hidden rounded-2xl border-2 border-slate-200 p-2 bg-white shadow-inner">
                                    <img 
                                        src="{{ $qrisImage }}" 
                                        alt="QRIS Warung Nasi Kuning Berkah" 
                                        class="mx-auto h-64 w-64 object-contain sm:h-72 sm:w-72"
                                    >
                                </div>

                                <div class="mt-3">
                                    <p class="text-xs text-slate-500">Total yang harus ditransfer:</p>
                                    <p class="text-2xl font-black text-amber-700">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                </div>

                                <!-- Tombol WAJIB Kirim Bukti ke WhatsApp -->
                                @if ($whatsappUrl)
                                    <a 
                                        href="{{ $whatsappUrl }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="mt-5 flex w-full items-center justify-center gap-2.5 rounded-2xl bg-[#25D366] px-6 py-4 text-center text-sm font-black text-white shadow-lg shadow-green-600/30 transition hover:bg-[#20ba5a]"
                                    >
                                        <span class="text-lg">💬</span>
                                        <span>Kirim Bukti Pembayaran ke WhatsApp</span>
                                    </a>
                                @endif

                                <!-- Opsi Alternatif: Upload Langsung di Web -->
                                @if (! $transaction->receipt_image && $transaction->payment_status !== 'paid' && $proofUrl)
                                    <div class="mt-4 border-t border-slate-100 pt-4 text-left">
                                        <label for="payment_proof" class="block text-xs font-bold text-slate-700 mb-1.5">Screenshot bukti QRIS (Opsional Upload di Web):</label>
                                        <form method="POST" action="{{ $proofUrl }}" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            <input id="payment_proof" name="payment_proof" type="file" accept="image/jpeg,image/png,image/webp" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 p-2 text-xs text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1 file:text-xs file:font-bold file:text-slate-900">
                                            @error('payment_proof')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                                            <button type="submit" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-bold text-white hover:bg-slate-700">
                                                Unggah bukti pembayaran
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($transaction->receipt_image)
                                    <p class="mt-4 rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-800">
                                        ✓ Bukti pembayaran telah diunggah ke sistem.
                                    </p>
                                @endif
                            </div>
                        @else
                            <!-- Bayar di Warung -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-emerald-50 text-4xl text-emerald-600">
                                    🏪
                                </div>
                                <h2 class="mt-4 text-lg font-black text-slate-900">Bayar langsung di warung</h2>
                                <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                    Pesanan Anda telah masuk ke kasir warung. Silakan datang langsung ke warung untuk mengambil pesanan dan melakukan pembayaran tunai atau QRIS di kasir.
                                </p>

                                @if ($whatsappUrl)
                                    <a 
                                        href="{{ $whatsappUrl }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-[#25D366] px-5 py-3.5 text-center text-xs sm:text-sm font-black text-white shadow-md shadow-green-600/20 hover:bg-[#20ba5a]"
                                    >
                                        <span>💬 Konfirmasi Pesanan via WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6 text-center">
                    <a href="{{ route('home') }}" class="rounded-full bg-slate-100 px-6 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
