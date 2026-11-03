# Issue: Konflik Nilai Unik Saat Update Menu

Status: selesai diperbaiki dan diuji ulang.

## Masalah

Saat mengubah data menu, nilai `kode_menu` atau `barcode` milik menu lain berpotensi lolos validasi jika pengecekan duplikat hanya dilakukan pada proses Create. Akibatnya, pengguna baru mengetahui konflik setelah query `UPDATE` gagal, atau data dapat tertimpa jika constraint basis data tidak aktif.

## Langkah Pemeriksaan

1. Jalankan aplikasi dan buka `/menu`.
2. Klik **Edit** pada menu `MN00000001` (Nasi Goreng).
3. Ubah `kode_menu` menjadi `MN00000002` atau `barcode` menjadi `BR-MNM-0001`, yaitu nilai milik menu Es Teh.
4. Kirim form.
5. Perhatikan pesan validasi dan pastikan data menu Es Teh tidak berubah.

## Perbaikan

- Menambahkan pemeriksaan duplikat dengan PDO prepared statement pada `MenuRepository::codeExists()` dan `MenuRepository::barcodeExists()`.
- Pemeriksaan pada proses update memakai kondisi `id <> :ignore_id` agar nilai milik data yang sedang diedit tidak dianggap duplikat.
- Menambahkan pesan validasi pada field `kode_menu` dan `barcode`.
- Mempertahankan constraint `UNIQUE` pada basis data sebagai pengaman terakhir.
- Menangani `SQLSTATE 23000` dengan pesan aman tanpa membocorkan detail query.

## Hasil Uji Ulang

- Update dengan kode menu milik data lain ditolak dan menampilkan pesan **“Kode menu sudah digunakan.”**
- Update dengan barcode milik data lain ditolak dan menampilkan pesan **“Barcode sudah digunakan.”**
- Data asli tidak berubah setelah percobaan update yang gagal.
- Pengujian otomatis `menolak nilai unik milik data lain saat update` lulus.

Perintah pengujian:

```bash
php artisan test --compact --filter="menolak nilai unik milik data lain saat update"
```
