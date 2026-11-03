<?php

if (! function_exists('statusHarga')) {
    /**
     * Menentukan status harga menu sesuai ketentuan Tipe E.
     */
    function statusHarga(int $harga): string
    {
        return $harga >= 20000 ? 'Premium' : 'Reguler';
    }
}
