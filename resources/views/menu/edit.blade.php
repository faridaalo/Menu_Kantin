@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')
    <h2>Edit Menu</h2>

    <p>Data menu: <strong>{{ $menu['nama_menu'] }}</strong> ({{ $menu['kode_menu'] }})</p>

    <form action="{{ route('menu.update', $menu['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_menu">Kode Menu (tepat 10 karakter alfanumerik)</label><br>
            <input type="text" id="kode_menu" name="kode_menu" value="{{ old('kode_menu', $menu['kode_menu']) }}" maxlength="10" required>
        </p>

        <p>
            <label for="nama_menu">Nama Menu (3&ndash;100 karakter)</label><br>
            <input type="text" id="nama_menu" name="nama_menu" value="{{ old('nama_menu', $menu['nama_menu']) }}" maxlength="100" required>
        </p>

        <p>
            <label for="barcode">Barcode (5&ndash;30 karakter)</label><br>
            <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $menu['barcode']) }}" maxlength="30" required>
        </p>

        <p>
            <label for="harga">Harga (Rp1.000&ndash;Rp100.000)</label><br>
            <input type="number" id="harga" name="harga" value="{{ old('harga', $menu['harga']) }}" min="1000" max="100000" required>
        </p>

        <p>
            <label for="kategori_menu_id">Kategori Menu</label><br>
            <select id="kategori_menu_id" name="kategori_menu_id" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach ($categories as $kategori)
                    <option value="{{ $kategori['id'] }}" @selected((string) old('kategori_menu_id', $menu['kategori_menu_id']) === (string) $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Perbarui Menu</button>
        <a href="{{ route('menu.index') }}">Batal</a>
    </form>
@endsection
