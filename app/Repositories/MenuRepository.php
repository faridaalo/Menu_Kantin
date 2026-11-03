<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use PDO;

class MenuRepository
{
    /**
     * Mengambil koneksi PDO dari koneksi Laravel.
     */
    private function pdo(): PDO
    {
        return DB::connection()->getPdo();
    }

    /**
     * @return array<int, array{id: int|string, nama_kategori: string}>
     */
    public function allCategories(): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nama_kategori FROM kategori_menu ORDER BY nama_kategori ASC'
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array{id: int|string, nama_kategori: string, jumlah_menu: int|string}>
     */
    public function categorySummary(): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT k.id, k.nama_kategori, COUNT(m.id) AS jumlah_menu
             FROM kategori_menu k
             LEFT JOIN menu m ON m.kategori_menu_id = k.id
             GROUP BY k.id, k.nama_kategori
             ORDER BY k.nama_kategori ASC'
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(?string $keyword, ?int $categoryId): array
    {
        $sql = 'SELECT m.id, m.kode_menu, m.nama_menu, m.barcode, m.harga,
                       m.kategori_menu_id, m.created_at, m.updated_at,
                       k.nama_kategori
                FROM menu m
                INNER JOIN kategori_menu k ON k.id = m.kategori_menu_id';
        $conditions = [];
        $bindings = [];

        if ($keyword !== null && $keyword !== '') {
            $conditions[] = '(m.kode_menu LIKE :kode_menu
                OR m.nama_menu LIKE :nama_menu
                OR m.barcode LIKE :barcode)';
            $bindings[':kode_menu'] = '%'.$keyword.'%';
            $bindings[':nama_menu'] = '%'.$keyword.'%';
            $bindings[':barcode'] = '%'.$keyword.'%';
        }

        if ($categoryId !== null) {
            $conditions[] = 'm.kategori_menu_id = :kategori_menu_id';
            $bindings[':kategori_menu_id'] = $categoryId;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY m.id DESC';

        $statement = $this->pdo()->prepare($sql);

        foreach ($bindings as $name => $value) {
            $statement->bindValue(
                $name,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, kode_menu, nama_menu, barcode, harga, kategori_menu_id, created_at, updated_at
             FROM menu
             WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $menu = $statement->fetch(PDO::FETCH_ASSOC);

        return $menu === false ? null : $menu;
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        return $this->uniqueValueExists('kode_menu', $code, $ignoreId);
    }

    public function barcodeExists(string $barcode, ?int $ignoreId = null): bool
    {
        return $this->uniqueValueExists('barcode', $barcode, $ignoreId);
    }

    private function uniqueValueExists(string $column, string $value, ?int $ignoreId): bool
    {
        $sql = sprintf('SELECT COUNT(*) FROM menu WHERE %s = :value', $column);

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
        }

        $statement = $this->pdo()->prepare($sql);
        $statement->bindValue(':value', $value, PDO::PARAM_STR);

        if ($ignoreId !== null) {
            $statement->bindValue(':ignore_id', $ignoreId, PDO::PARAM_INT);
        }

        $statement->execute();

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @param  array{kode_menu: string, nama_menu: string, barcode: string, harga: int, kategori_menu_id: int}  $data
     */
    public function create(array $data): void
    {
        $now = date('Y-m-d H:i:s');

        $statement = $this->pdo()->prepare(
            'INSERT INTO menu
                (kode_menu, nama_menu, barcode, harga, kategori_menu_id, created_at, updated_at)
             VALUES
                (:kode_menu, :nama_menu, :barcode, :harga, :kategori_menu_id, :created_at, :updated_at)'
        );

        $statement->bindValue(':kode_menu', $data['kode_menu'], PDO::PARAM_STR);
        $statement->bindValue(':nama_menu', $data['nama_menu'], PDO::PARAM_STR);
        $statement->bindValue(':barcode', $data['barcode'], PDO::PARAM_STR);
        $statement->bindValue(':harga', $data['harga'], PDO::PARAM_INT);
        $statement->bindValue(':kategori_menu_id', $data['kategori_menu_id'], PDO::PARAM_INT);
        $statement->bindValue(':created_at', $now, PDO::PARAM_STR);
        $statement->bindValue(':updated_at', $now, PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @param  array{kode_menu: string, nama_menu: string, barcode: string, harga: int, kategori_menu_id: int}  $data
     */
    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo()->prepare(
            'UPDATE menu
             SET kode_menu = :kode_menu,
                 nama_menu = :nama_menu,
                 barcode = :barcode,
                 harga = :harga,
                 kategori_menu_id = :kategori_menu_id,
                 updated_at = :updated_at
             WHERE id = :id'
        );

        $statement->bindValue(':kode_menu', $data['kode_menu'], PDO::PARAM_STR);
        $statement->bindValue(':nama_menu', $data['nama_menu'], PDO::PARAM_STR);
        $statement->bindValue(':barcode', $data['barcode'], PDO::PARAM_STR);
        $statement->bindValue(':harga', $data['harga'], PDO::PARAM_INT);
        $statement->bindValue(':kategori_menu_id', $data['kategori_menu_id'], PDO::PARAM_INT);
        $statement->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo()->prepare('DELETE FROM menu WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
