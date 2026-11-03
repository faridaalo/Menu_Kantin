<?php

it('menentukan status harga premium dan reguler', function (int $harga, string $status) {
    expect(statusHarga($harga))->toBe($status);
})->with([
    'batas premium' => [20000, 'Premium'],
    'harga maksimum' => [100000, 'Premium'],
    'di bawah premium' => [19999, 'Reguler'],
    'harga minimum' => [1000, 'Reguler'],
]);
