@extends('layouts.app')

@section('title', 'Daftar Menu Kantin')

@section('content')
    <h2>Daftar Menu</h2>

    @if ($error)
        <p><strong>Gagal:</strong> {{ $error }}</p>
    @endif

    <p>Total menu yang ditampilkan: <strong>{{ count($menus) }}</strong></p>

    <section>
        <h3>Tambah Menu</h3>
        <form action="{{ route('menu.store') }}" method="POST">
            @csrf

            <p>
                <label for="kode_menu">Kode Menu (tepat 10 karakter alfanumerik)</label><br>
                <input type="text" id="kode_menu" name="kode_menu" value="{{ old('kode_menu') }}" maxlength="10" required>
            </p>

            <p>
                <label for="nama_menu">Nama Menu (3&ndash;100 karakter)</label><br>
                <input type="text" id="nama_menu" name="nama_menu" value="{{ old('nama_menu') }}" maxlength="100" required>
            </p>

            <p>
                <label for="barcode">Barcode (5&ndash;30 karakter)</label><br>
                <input type="text" id="barcode" name="barcode" value="{{ old('barcode') }}" maxlength="30" required>
            </p>

            <p>
                <label for="harga">Harga (Rp1.000&ndash;Rp100.000)</label><br>
                <input type="number" id="harga" name="harga" value="{{ old('harga') }}" min="1000" max="100000" required>
            </p>

            <p>
                <label for="kategori_menu_id">Kategori Menu</label><br>
                <select id="kategori_menu_id" name="kategori_menu_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $kategori)
                        <option value="{{ $kategori['id'] }}" @selected((string) old('kategori_menu_id') === (string) $kategori['id'])>
                            {{ $kategori['nama_kategori'] }}
                        </option>
                    @endforeach
                </select>
            </p>

            <button type="submit">Simpan Menu</button>
        </form>
    </section>

    <hr>

    <section>
        <h3>Pencarian dan Filter</h3>
        <form action="{{ route('menu.index') }}" method="GET">
            <p>
                <label for="q">Kata Kunci (kode menu, nama menu, atau barcode)</label><br>
                <input type="text" id="q" name="q" value="{{ $keyword }}">
            </p>

            <p>
                <label for="filter_kategori">Filter Kategori Menu</label><br>
                <select id="filter_kategori" name="kategori_menu_id">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $kategori)
                        <option value="{{ $kategori['id'] }}" @selected((string) $selectedCategory === (string) $kategori['id'])>
                            {{ $kategori['nama_kategori'] }}
                        </option>
                    @endforeach
                </select>
            </p>

            <button type="submit">Cari</button>
            <a href="{{ route('menu.index') }}">Reset</a>
        </form>
    </section>

    <hr>

    <section>
        <h3>Tabel Data Menu</h3>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th>Barcode</th>
                    <th>Harga</th>
                    <th>Status Harga</th>
                    <th>Kategori Menu</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    @php
                        $status = statusHarga((int) $menu['harga']);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $menu['kode_menu'] }}</td>
                        <td>{{ $menu['nama_menu'] }}</td>
                        <td>{{ $menu['barcode'] }}</td>
                        <td>Rp{{ number_format((int) $menu['harga'], 0, ',', '.') }}</td>
                        <td>
                            @if ($status === 'Premium')
                                <strong>Premium</strong>
                            @else
                                Reguler
                            @endif
                        </td>
                        <td>{{ $menu['nama_kategori'] }}</td>
                        <td>{{ $menu['created_at'] }}</td>
                        <td>
                            <a href="{{ route('menu.edit', $menu['id']) }}">Edit</a>

                            <form action="{{ route('menu.destroy', $menu['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Tidak ada data menu yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <hr>

    <section>
        <h3>Tabel Master: Kategori Menu</h3>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Menu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categorySummary as $kategori)
                    <tr>
                        <td>{{ $kategori['id'] }}</td>
                        <td>{{ $kategori['nama_kategori'] }}</td>
                        <td>{{ $kategori['jumlah_menu'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada data kategori menu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
