# Sistem Menu Kantin

Aplikasi CRUD sederhana untuk mengelola menu kantin berdasarkan kategori menu. Proyek ini dibuat untuk **Praktikum Pemrograman Web, Paket 2, Tipe E**.

| Keterangan | Isi |
| --- | --- |
| Nama | Munfarida |
| NIM | 2401020166 |
| Tipe soal | E - Sistem Menu Kantin |
| Basis data | `menu_kantin_2401020166` |
| Route utama | `/menu` |
| Teknologi | Laravel 12, PHP 8.2, MariaDB/MySQL, PDO, Blade |

## Fitur

- Menampilkan daftar menu beserta nama kategori dari tabel master.
- Menambah menu dengan validasi server-side, `@csrf`, dan `old()`.
- Memperbarui menu melalui `PUT` dan method spoofing `@method('PUT')`.
- Menghapus menu melalui `DELETE` dan method spoofing `@method('DELETE')`.
- Menolak kode menu dan barcode yang sudah digunakan.
- Mencari menu berdasarkan `kode_menu`, `nama_menu`, atau `barcode`.
- Memfilter menu berdasarkan `kategori_menu_id`.
- Menampilkan feedback keberhasilan, pesan validasi, dan pesan kesalahan yang aman.
- Menampilkan status harga melalui fungsi `statusHarga()`:
  - `Premium` jika harga >= Rp20.000.
  - `Reguler` jika harga < Rp20.000.
- Menampilkan data tabel master `kategori_menu` pada halaman indeks.

## Rancangan Basis Data

### Tabel `kategori_menu`

| Kolom | Tipe | Ketentuan |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED | Primary key, auto increment |
| `nama_kategori` | VARCHAR(100) | NOT NULL, UNIQUE |
| `created_at` | TIMESTAMP | NULL |
| `updated_at` | TIMESTAMP | NULL |

### Tabel `menu`

| Kolom | Tipe | Ketentuan |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED | Primary key, auto increment |
| `kode_menu` | CHAR(10) | NOT NULL, UNIQUE |
| `nama_menu` | VARCHAR(100) | NOT NULL |
| `barcode` | VARCHAR(30) | NOT NULL, UNIQUE |
| `harga` | INT UNSIGNED | NOT NULL, CHECK `harga BETWEEN 1000 AND 100000` |
| `kategori_menu_id` | BIGINT UNSIGNED | NOT NULL, foreign key ke `kategori_menu.id` |
| `created_at` | TIMESTAMP | NULL |
| `updated_at` | TIMESTAMP | NULL |

Relasi: satu `kategori_menu` memiliki banyak `menu` (one-to-many). Foreign key menggunakan `ON UPDATE CASCADE` dan `ON DELETE RESTRICT`.

DDL dan DML lengkap tersedia pada [`database/sql/menu_kantin_2401020166.sql`](database/sql/menu_kantin_2401020166.sql).

### Data awal

| Kode Menu | Nama Menu | Barcode | Harga | Kategori |
| --- | --- | --- | --- | --- |
| MN00000001 | Nasi Goreng | BR-MKN-0001 | 18000 | Makanan |
| MN00000002 | Es Teh | BR-MNM-0001 | 5000 | Minuman |
| MN00000003 | Mie Rebus | BR-MKN-0002 | 15000 | Makanan |

## Route

| Method | URI | Name | Kegunaan |
| --- | --- | --- | --- |
| GET | `/` | - | Dialihkan ke `/menu` |
| GET | `/menu` | `menu.index` | Indeks, pencarian, filter, form tambah, tabel data |
| POST | `/menu` | `menu.store` | Menyimpan menu baru |
| GET | `/menu/{id}/edit` | `menu.edit` | Menampilkan form edit |
| PUT | `/menu/{id}` | `menu.update` | Memperbarui menu |
| DELETE | `/menu/{id}` | `menu.destroy` | Menghapus menu |

## Cara Menjalankan

1. Pastikan PHP 8.2+, Composer, dan MySQL/MariaDB sudah tersedia.
2. Buat basis data:

   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS menu_kantin_2401020166 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. Salin konfigurasi lingkungan dan sesuaikan kredensial basis data:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. Jalankan migrasi dan seeder:

   ```bash
   php artisan migrate:fresh --seed
   ```

   Alternatif: impor berkas SQL.

   ```bash
   mysql -u root < database/sql/menu_kantin_2401020166.sql
   ```

5. Jalankan server:

   ```bash
   php artisan serve
   ```

6. Buka `http://localhost:8000/menu`.

## Penerapan PDO dan Prepared Statement

Akses basis data dilakukan melalui `App\Repositories\MenuRepository`. Repository mengambil koneksi PDO dari Laravel:

```php
private function pdo(): PDO
{
    return DB::connection()->getPdo();
}
```

Semua query yang menerima input pengguna menggunakan `prepare()` dan parameter terikat, bukan penggabungan string SQL. Contoh pencarian:

```php
$statement = $this->pdo()->prepare(
    'SELECT ... FROM menu m
     INNER JOIN kategori_menu k ON k.id = m.kategori_menu_id
     WHERE (m.kode_menu LIKE :kode_menu OR m.nama_menu LIKE :nama_menu OR m.barcode LIKE :barcode)'
);
```

Nilai `kode_menu`, `nama_menu`, dan `barcode` dikirim melalui `bindValue()` sehingga aman dari SQL injection.

## Sanitasi, Validasi, dan Pencegahan Duplikat

- Input teks dibersihkan dengan `trim()` dan `strip_tags()` sebelum divalidasi.
- Validasi server-side:
  - `kode_menu`: wajib, tepat 10 karakter, alfanumerik.
  - `nama_menu`: wajib, 3-100 karakter.
  - `barcode`: wajib, 5-30 karakter.
  - `harga`: wajib, bilangan bulat, 1.000-100.000.
  - `kategori_menu_id`: wajib, bilangan bulat, harus ada di `kategori_menu`.
- Kode menu dan barcode diperiksa dengan prepared statement. Saat update, pengecekan mengecualikan ID data yang sedang diubah agar nilai milik sendiri tidak dianggap duplikat.
- Constraint `UNIQUE` pada basis data menjadi pengaman terakhir jika terjadi race condition.
- Form menggunakan `@csrf`, `old()`, `@method('PUT')`, dan `@method('DELETE')`.

## Error Handling

Operasi basis data dibungkus `try-catch`. Detail kesalahan dicatat dengan `report($exception)`, sedangkan pengguna hanya menerima pesan aman:

> Terjadi kesalahan saat memproses data. Silakan coba lagi.

Pelanggaran constraint unik (`SQLSTATE 23000`) ditampilkan sebagai pesan validasi kode menu/barcode tanpa membocorkan detail teknis.

## Pengujian

Jalankan seluruh pengujian:

```bash
php artisan test --compact
```

Sepuluh skenario wajib diuji pada `tests/Feature/MenuCrudTest.php`:

| No. | Skenario | Bukti pengujian |
| --- | --- | --- |
| 1 | Read tiga data awal dan tabel master | Data awal dan kategori tampil pada indeks |
| 2 | Create valid | Data tersimpan dan feedback sukses tampil |
| 3 | Create tidak valid | Pesan kesalahan tampil dan input lama dipertahankan |
| 4 | Duplikat | Kode menu dan barcode yang sama ditolak |
| 5 | Update | Data lama tampil pada form edit dan perubahan tersimpan |
| 6 | Konflik update | Nilai unik milik data lain ditolak |
| 7 | Delete | Data terhapus melalui form DELETE dengan CSRF |
| 8 | Pencarian | Tiga kolom dapat dicari |
| 9 | Filter | Hasil dibatasi berdasarkan kategori menu |
| 10 | Error handling | Kesalahan dicatat dan pengguna menerima pesan aman |

Fungsi `statusHarga()` diuji pada `tests/Unit/StatusHargaTest.php`.

## GitHub Issue

Draf isi GitHub Issue tersedia pada [`docs/issue-01-konflik-update.md`](docs/issue-01-konflik-update.md). Isu tersebut mencatat masalah konflik nilai unik saat update, langkah pemeriksaan, perbaikan, dan hasil uji ulang.

## Struktur Berkas Penting

```text
app/
  Http/Controllers/MenuController.php   # Validasi, feedback, error handling
  Repositories/MenuRepository.php      # PDO dan prepared statement
  Support/helpers.php                  # Fungsi statusHarga()
database/
  migrations/                          # Skema dua tabel
  seeders/MenuSeeder.php               # Data awal wajib
  sql/menu_kantin_2401020166.sql       # DDL + DML lengkap
docs/
  issue-01-konflik-update.md           # Draf GitHub Issue
resources/views/
  layouts/app.blade.php                # Layout HTML sederhana tanpa CSS/JS
  menu/index.blade.php                 # Form tambah, pencarian, filter, tabel
  menu/edit.blade.php                  # Form edit
routes/web.php                         # Lima route CRUD
tests/                                 # Pengujian Pest
```
