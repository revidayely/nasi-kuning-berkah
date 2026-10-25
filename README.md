# Sistem Website & POS Warung Nasi Kuning Berkah (Laravel 12)

Sistem Informasi Terintegrasi **Warung Nasi Kuning Berkah**: Menggabungkan **Landing Page Promosi Pelanggan** + **POS Kasir Internal & Dapur** + **Inventori Stok Harian** + **Dashboard Analitik & Laporan Owner**.

Dibangun dengan **Laravel 12**, Blade, Tailwind CSS, Vite, dan MySQL / SQLite.

---

## 🚀 Fitur Unggulan Sistem

### 1. Customer Facing (Landing Page Promosi & Checkout)
- **Hero & Branding Autentik:** Tagline *"Nasi Kuning Autentik, Cita Rasa Berkah"*, banner promo Paket Komplit, dan 3 nilai utama (Resep turun-temurun sejak 2008, Fresh dimasak subuh 05.00, Layanan kotakan/syukuran).
- **Showcase Menu & Filter Kategori:** Menu terfilter otomatis (*Semua*, *Paket Nasi*, *Lauk Tambahan*, *Minuman*), foto hidangan, indikator stok harian realtime (*Tersedia / Sisa X / Habis*).
- **Keranjang Belanja & Checkout Mandiri:** Keranjang responsif, estimasi total, pilihan metode pembayaran (*Di Warung* atau *QRIS E-Wallet*).
- **Cerita Rasa, Galeri Foto Warung, & Ulasan Tamu:** Menampilkan kehangatan dapur, ulasan bintang 5 tamu, alamat fisik, jam operasional (05.30 - 13.00 WIB), dan integrasi Google Maps & WhatsApp.
- **Konfirmasi Order WhatsApp & Unggah Bukti QRIS:** Tombol WhatsApp sekali klik dengan draf pesan otomatis dan halaman unggah bukti transfer QRIS terlindungi signed URL.

### 2. POS Kasir Internal & Pemantauan Dapur (`/pos`)
- **Terminal Kasir POS Langsung:**
  - Grid menu cepat dengan pencarian dan filter kategori lauk.
  - Pilihan tipe pesanan: *🍽️ Makan di Tempat (Dine-in)* atau *🥡 Bawa Pulang (Takeaway)*.
  - Perhitungan subtotal, kalkulator kembalian uang tunai, dan tombol cepat nominal (`[Uang Pas]`, `[20k]`, `[50k]`, `[100k]`).
  - **Auto-Deduct Stock Atomik:** Stok lauk berkurang secara atomik dalam database transaction saat kasir menyelesaikan pesanan.
  - **Cetak Struk Thermal (58mm / 80mm):** Layout cetak struk standar printer kasir thermal dengan tombol cetak langsung (`window.print()`).
- **Antrean Pesanan Online & Kitchen Display:**
  - Pemantauan pesanan masuk dari website secara realtime.
  - Status dapur: *Pesanan Baru* ➔ *Sedang Disiapkan* ➔ *Siap Diambil* ➔ *Selesai*.
  - Verifikasi bukti transfer QRIS oleh kasir sebelum konfirmasi pembayaran dan pemotongan stok.

### 3. Modul Inventori & Kelola Stok Harian (`/stock`)
- **Ringkasan KPI Inventori:** Total menu aktif, stok aman, butuh perhatian (menipis), dan stok habis.
- **Banner Peringatan Menipis:** Notifikasi otomatis ketika stok lauk berada di bawah batas minimum (`minimum_stock`).
- **Pembaruan Stok Cepat:** Tombol penyesuaian porsi cepat `+5` / `-5` dan input langsung dengan sekali klik simpan.

### 4. Dashboard Analitik & Laporan Penjualan Owner (`/dashboard`)
- **Filter Rentang Waktu Komprehensif:** *Hari Ini*, *Kemarin*, *7 Hari Terakhir*, *Bulan Ini*, atau *Pilihan Rentang Tanggal Custom*.
- **Metrik Bisnis Utama:** Total Omset Terverifikasi, Jumlah Transaksi Selesai, Nilai Rata-rata Pesanan (AOV), dan Pesanan Pending.
- **Grafik Tren Omset 7 Hari:** Visual bar chart fluktuasi pendapatan harian warung.
- **Top 5 Menu Terlaris:** Peringkat menu paling diminati beserta jumlah porsi terjual dan kontribusi omsetnya.
- **Ekspor Laporan ke CSV / Excel:** Unduh rekapitulasi transaksi untuk pembukuan akuntansi.
- **Cetak Dokumen Rekap Laporan Penjualan:** Format laporan siap cetak ukuran A4 dengan tanda tangan pemilik.

### 5. Manajemen Staf & Kasir (`/manage/staff`)
- Khusus diakses oleh role `owner`.
- Daftarkan akun kasir baru atau owner partner.
- Reset password staf melalui antarmuka modal tanpa perlu akses command line.
- Hapus akun staf dengan proteksi akun mandiri.

---

## 🛠️ Prasyarat Lingkungan

- **PHP 8.2+** (Direkomendasikan PHP 8.4 yang tersedia di Laragon) dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`.
- **Composer 2**
- **Node.js LTS & npm**
- **MySQL 8.0+ / MariaDB**

---

## ⚙️ Panduan Setup Lokal

1. **Konfigurasi Environment Database:**
   Salin `.env.example` ke `.env` dan sesuaikan parameter database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_nasi_kuning
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **Generate Key & Migrasi Data:**
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
   *Seeder akan otomatis membuat akun staf default dan katalog menu lengkap beserta stok harian.*

3. **Akun Pengguna Default (Lokal / Demo):**
   - **Owner:** `owner@warung.test` | Password: `password`
   - **Kasir:** `kasir@warung.test` | Password: `password`

4. **Kompilasi Frontend:**
   ```bash
   npm install
   npm run build
   ```

5. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```
   Akses di browser: `http://localhost:8000`

---

## 🧪 Pengujian Otomatis (Automated Testing)

Seluruh alur fitur inti telah diuji secara komprehensif menggunakan test suite bawaan Laravel:

```bash
php artisan test
```

**Hasil Pengujian:**
- **47 Tests Passed**
- **191 Assertions Passed**
- Mencakup: Autentikasi & Role Permission, Alur Checkout QRIS & Warung, Validasi Stok & Pencegahan Overselling, Transaksi Langsung POS Kasir, Cetak Struk Thermal, Manajemen Staf Owner, dan Ekspor Laporan.

---

## 🚀 Panduan Siap Deploy ke Production

1. **Persiapan Server Production:**
   - Atur `.env` production:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://naskunberkah.com
     WARUNG_WHATSAPP=6287848361913
     WARUNG_QRIS_IMAGE=https://naskunberkah.com/images/qris-resmi.png
     ```
2. **Kompilasi Aset & Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
3. **Migrasi Database Production:**
   ```bash
   php artisan migrate --force
   ```
4. **Optimasi Cache Laravel:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   view:cache
   ```
5. **Jalankan Cron Scheduler:**
   Jadwalkan cron job per menit untuk membatalkan pesanan pending yang kedaluwarsa:
   ```bash
   * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
   ```

## 🧪 Pengujian Otomatis (Automated Testing)

Seluruh alur fitur inti telah diuji secara komprehensif menggunakan test suite bawaan Laravel:

```bash
php artisan test
```

**Hasil Pengujian:**
- **47 Tests Passed**
- **191 Assertions Passed**
- Mencakup: Autentikasi & Role Permission, Alur Checkout QRIS & Warung, Validasi Stok & Pencegahan Overselling, Transaksi Langsung POS Kasir, Cetak Struk Thermal, Manajemen Staf Owner, dan Ekspor Laporan.

---

## 🚀 Panduan Siap Deploy ke Production

1. **Persiapan Server Production:**
   - Atur `.env` production:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://naskunberkah.com
     WARUNG_WHATSAPP=6287848361913
     WARUNG_QRIS_IMAGE=https://naskunberkah.com/images/qris-resmi.png
     ```
2. **Kompilasi Aset & Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
3. **Migrasi Database Production:**
   ```bash
   php artisan migrate --force
   ```
4. **Optimasi Cache Laravel:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   view:cache
   ```
5. **Jalankan Cron Scheduler:**
   Jadwalkan cron job per menit untuk membatalkan pesanan pending yang kedaluwarsa:
   ```bash
   * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
   ```

