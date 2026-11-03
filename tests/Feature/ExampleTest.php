<?php

test('halaman utama mengarah ke daftar menu', function () {
    $this->get('/')->assertRedirect(route('menu.index'));
});
