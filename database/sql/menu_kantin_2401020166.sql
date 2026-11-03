-- ============================================================
-- Praktikum Pemrograman Web - Paket 2 - Tipe E (Menu Kantin)
-- Nama  : Munfarida
-- NIM   : 2401020166
-- Basis data: menu_kantin_2401020166
-- ============================================================

CREATE DATABASE IF NOT EXISTS menu_kantin_2401020166
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE menu_kantin_2401020166;

DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS kategori_menu;

-- ------------------------------------------------------------
-- DDL: tabel master kategori_menu
-- ------------------------------------------------------------
CREATE TABLE kategori_menu (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DDL: tabel utama menu
-- ------------------------------------------------------------
CREATE TABLE menu (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_menu CHAR(10) NOT NULL UNIQUE,
    nama_menu VARCHAR(100) NOT NULL,
    barcode VARCHAR(30) NOT NULL UNIQUE,
    harga INT UNSIGNED NOT NULL,
    kategori_menu_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_menu_kategori_menu
        FOREIGN KEY (kategori_menu_id) REFERENCES kategori_menu (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_menu_harga CHECK (harga BETWEEN 1000 AND 100000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DML: data awal wajib
-- ------------------------------------------------------------
INSERT INTO kategori_menu (nama_kategori, created_at, updated_at) VALUES
    ('Makanan', NOW(), NOW()),
    ('Minuman', NOW(), NOW());

SET @kategori_makanan = (SELECT id FROM kategori_menu WHERE nama_kategori = 'Makanan');
SET @kategori_minuman = (SELECT id FROM kategori_menu WHERE nama_kategori = 'Minuman');

INSERT INTO menu (kode_menu, nama_menu, barcode, harga, kategori_menu_id, created_at, updated_at) VALUES
    ('MN00000001', 'Nasi Goreng', 'BR-MKN-0001', 18000, @kategori_makanan, NOW(), NOW()),
    ('MN00000002', 'Es Teh', 'BR-MNM-0001', 5000, @kategori_minuman, NOW(), NOW()),
    ('MN00000003', 'Mie Rebus', 'BR-MKN-0002', 15000, @kategori_makanan, NOW(), NOW());
