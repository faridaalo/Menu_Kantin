<?php

use App\Repositories\MenuRepository;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(MenuSeeder::class);
});

/**
 * @return array<string, int|string>
 */
function payloadMenu(): array
{
    return [
        'kode_menu' => 'MN00000004',
        'nama_menu' => 'Soto Ayam',
        'barcode' => 'BR-MKN-0003',
        'harga' => 25000,
        'kategori_menu_id' => (int) DB::table('kategori_menu')
            ->where('nama_kategori', 'Makanan')
            ->value('id'),
    ];
}

it('menampilkan tiga data awal dan tabel master kategori pada indeks', function () {
    $response = $this->get(route('menu.index'));

    $response->assertOk()
        ->assertSee('Nasi Goreng')
        ->assertSee('Es Teh')
        ->assertSee('Mie Rebus')
        ->assertSee('Tabel Master: Kategori Menu')
        ->assertSee('Makanan')
        ->assertSee('Minuman');
});

it('menyimpan menu baru yang valid dan menampilkan feedback sukses', function () {
    $response = $this->post(route('menu.store'), payloadMenu());

    $response->assertRedirect(route('menu.index'))
        ->assertSessionHas('sukses', 'Menu berhasil ditambahkan.');

    $this->assertDatabaseHas('menu', [
        'kode_menu' => 'MN00000004',
        'nama_menu' => 'Soto Ayam',
        'barcode' => 'BR-MKN-0003',
        'harga' => 25000,
    ]);

    $this->get(route('menu.index'))
        ->assertOk()
        ->assertSee('Soto Ayam')
        ->assertSee('Premium');
});

it('menolak input tidak valid dan mempertahankan input lama', function () {
    $response = $this->from(route('menu.index'))->post(route('menu.store'), [
        'kode_menu' => 'MN01',
        'nama_menu' => 'ab',
        'barcode' => 'BR1',
        'harga' => 500,
        'kategori_menu_id' => '',
    ]);

    $response->assertRedirect(route('menu.index'))
        ->assertSessionHasErrors(['kode_menu', 'nama_menu', 'barcode', 'harga', 'kategori_menu_id'])
        ->assertSessionHasInput('nama_menu', 'ab');

    $this->assertDatabaseCount('menu', 3);
});

it('menolak kode menu atau barcode yang sudah digunakan', function () {
    $payload = payloadMenu();

    $this->from(route('menu.index'))
        ->post(route('menu.store'), array_merge($payload, [
            'kode_menu' => 'MN00000001',
        ]))
        ->assertSessionHasErrors('kode_menu');

    $this->from(route('menu.index'))
        ->post(route('menu.store'), array_merge($payload, [
            'barcode' => 'BR-MKN-0001',
        ]))
        ->assertSessionHasErrors('barcode');

    $this->assertDatabaseCount('menu', 3);
});

it('menampilkan data lama pada form edit', function () {
    $menuId = (int) DB::table('menu')->where('kode_menu', 'MN00000001')->value('id');

    $response = $this->get(route('menu.edit', $menuId));

    $response->assertOk()
        ->assertSee('Edit Menu')
        ->assertSee('Nasi Goreng')
        ->assertSee('MN00000001')
        ->assertSee('BR-MKN-0001')
        ->assertSee('name="_method"', false);
});

it('memperbarui menu dan menampilkan feedback sukses', function () {
    $menuId = (int) DB::table('menu')->where('kode_menu', 'MN00000001')->value('id');

    $response = $this->put(route('menu.update', $menuId), [
        'kode_menu' => 'MN00000011',
        'nama_menu' => 'Nasi Goreng Spesial',
        'barcode' => 'BR-MKN-0011',
        'harga' => 21000,
        'kategori_menu_id' => (int) DB::table('kategori_menu')
            ->where('nama_kategori', 'Makanan')
            ->value('id'),
    ]);

    $response->assertRedirect(route('menu.index'))
        ->assertSessionHas('sukses', 'Menu berhasil diperbarui.');

    $this->assertDatabaseHas('menu', [
        'id' => $menuId,
        'kode_menu' => 'MN00000011',
        'nama_menu' => 'Nasi Goreng Spesial',
        'barcode' => 'BR-MKN-0011',
        'harga' => 21000,
    ]);
});

it('menolak nilai unik milik data lain saat update', function () {
    $menuId = (int) DB::table('menu')->where('kode_menu', 'MN00000001')->value('id');

    $response = $this->from(route('menu.edit', $menuId))->put(route('menu.update', $menuId), [
        'kode_menu' => 'MN00000002',
        'nama_menu' => 'Nasi Goreng Spesial',
        'barcode' => 'BR-MNM-0001',
        'harga' => 21000,
        'kategori_menu_id' => (int) DB::table('kategori_menu')
            ->where('nama_kategori', 'Makanan')
            ->value('id'),
    ]);

    $response->assertRedirect(route('menu.edit', $menuId))
        ->assertSessionHasErrors(['kode_menu', 'barcode']);

    $this->assertDatabaseHas('menu', [
        'id' => $menuId,
        'kode_menu' => 'MN00000001',
        'barcode' => 'BR-MKN-0001',
    ]);
});

it('menghapus menu melalui form DELETE dengan CSRF', function () {
    $menuId = (int) DB::table('menu')->where('kode_menu', 'MN00000002')->value('id');

    $response = $this->delete(route('menu.destroy', $menuId));

    $response->assertRedirect(route('menu.index'))
        ->assertSessionHas('sukses', 'Menu berhasil dihapus.');

    $this->assertDatabaseMissing('menu', ['id' => $menuId]);
});

it('mencari menu berdasarkan kode, nama, dan barcode', function (string $keyword, string $expectedName) {
    $this->get(route('menu.index', ['q' => $keyword]))
        ->assertOk()
        ->assertSee($expectedName);
})->with([
    'kode menu' => ['MN00000002', 'Es Teh'],
    'nama menu' => ['Mie Rebus', 'Mie Rebus'],
    'barcode' => ['BR-MNM-0001', 'Es Teh'],
]);

it('memfilter menu berdasarkan kategori menu', function () {
    $minumanId = (int) DB::table('kategori_menu')
        ->where('nama_kategori', 'Minuman')
        ->value('id');

    $this->get(route('menu.index', ['kategori_menu_id' => $minumanId]))
        ->assertOk()
        ->assertSee('Es Teh')
        ->assertDontSee('Nasi Goreng')
        ->assertDontSee('Mie Rebus');
});

it('menampilkan pesan aman saat terjadi kesalahan basis data', function () {
    $this->mock(MenuRepository::class, function ($mock): void {
        $mock->shouldReceive('search')
            ->once()
            ->andThrow(new PDOException('Koneksi basis data gagal.'));
    });

    $this->get(route('menu.index'))
        ->assertOk()
        ->assertSee('Terjadi kesalahan saat memproses data. Silakan coba lagi.');
});
