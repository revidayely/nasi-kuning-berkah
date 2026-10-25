# Konsep Database

## Commit dan rollback

Operasi penting menggunakan `DB::transaction(...)`. Laravel melakukan commit saat callback selesai tanpa error dan rollback otomatis jika exception terjadi. Pola ini dipakai saat checkout, konfirmasi pembayaran, pembuatan menu beserta stok awal, dan pembuatan detail transaksi. Perintah migrasi rutin database di bawah khusus MySQL/MariaDB; SQLite tetap bisa dipakai untuk pengembangan dan pengujian.

## Function dan trigger

Migrasi `2026_10_26_000001_create_transaction_database_routines` membuat function `fn_transaction_line_subtotal(quantity, unit_price)` dan dua trigger `BEFORE INSERT` / `BEFORE UPDATE` pada `transaction_details`. Trigger mengisi `subtotal` dari kuantitas dikali harga satuan, sehingga nilai subtotal tetap benar meskipun detail dibuat atau diubah di luar aplikasi Laravel.

## Stored procedure

Procedure `sp_transaction_summary(transaction_id)` mengembalikan dua result set: ringkasan transaksi dengan total tersimpan dan hasil perhitungan ulang, lalu rincian item. Procedure ini berguna untuk audit rekonsiliasi struk.

Contoh pemanggilan dari SQL:

```sql
CALL sp_transaction_summary(1);
```

## Menjalankan

Di lingkungan MySQL/MariaDB, jalankan migrasi biasa: `php artisan migrate`. Akun database perlu izin untuk membuat function, trigger, dan procedure. Migrasi ini otomatis dilewati di SQLite. Objek rutin dibuat saat migrasi dan dihapus oleh rollback migrasi.