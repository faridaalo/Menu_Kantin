<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Seed data awal wajib untuk Tipe E.
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        DB::table('kategori_menu')->insertOrIgnore([
            [
                'nama_kategori' => 'Makanan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_kategori' => 'Minuman',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $makananId = DB::table('kategori_menu')
            ->where('nama_kategori', 'Makanan')
            ->value('id');

        $minumanId = DB::table('kategori_menu')
            ->where('nama_kategori', 'Minuman')
            ->value('id');

        DB::table('menu')->insertOrIgnore([
            [
                'kode_menu' => 'MN00000001',
                'nama_menu' => 'Nasi Goreng',
                'barcode' => 'BR-MKN-0001',
                'harga' => 18000,
                'kategori_menu_id' => $makananId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_menu' => 'MN00000002',
                'nama_menu' => 'Es Teh',
                'barcode' => 'BR-MNM-0001',
                'harga' => 5000,
                'kategori_menu_id' => $minumanId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_menu' => 'MN00000003',
                'nama_menu' => 'Mie Rebus',
                'barcode' => 'BR-MKN-0002',
                'harga' => 15000,
                'kategori_menu_id' => $makananId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
