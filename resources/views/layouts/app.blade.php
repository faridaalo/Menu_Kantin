<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Menu Kantin')</title>
</head>
<body>
    <header>
        <h1>Sistem Menu Kantin</h1>
        <nav>
            <a href="{{ route('menu.index') }}">Daftar Menu</a>
        </nav>
    </header>

    <hr>

    <main>
        @if (session('sukses'))
            <p><strong>Sukses:</strong> {{ session('sukses') }}</p>
        @endif

        @if (session('error'))
            <p><strong>Gagal:</strong> {{ session('error') }}</p>
        @endif

        @if ($errors->any())
            <div>
                <strong>Validasi gagal:</strong>
                <ul>
                    @foreach ($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <hr>

    <footer>
        <p>Sistem Menu Kantin &mdash; Praktikum Pemrograman Web</p>
    </footer>
</body>
</html>
