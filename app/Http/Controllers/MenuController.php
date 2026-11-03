<?php

namespace App\Http\Controllers;

use App\Repositories\MenuRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PDOException;
use Throwable;

class MenuController extends Controller
{
    private const SAFE_ERROR_MESSAGE = 'Terjadi kesalahan saat memproses data. Silakan coba lagi.';

    public function __construct(private readonly MenuRepository $menuRepository) {}

    public function index(Request $request): View
    {
        $keyword = trim(strip_tags((string) $request->query('q', '')));
        $keyword = $keyword === '' ? null : $keyword;

        $categoryInput = $request->query('kategori_menu_id');
        $categoryId = is_numeric($categoryInput) && (int) $categoryInput > 0
            ? (int) $categoryInput
            : null;

        $error = null;

        try {
            $menus = $this->menuRepository->search($keyword, $categoryId);
            $categories = $this->menuRepository->allCategories();
            $categorySummary = $this->menuRepository->categorySummary();
        } catch (Throwable $exception) {
            report($exception);

            $menus = [];
            $categories = [];
            $categorySummary = [];
            $error = self::SAFE_ERROR_MESSAGE;
        }

        return view('menu.index', [
            'menus' => $menus,
            'categories' => $categories,
            'categorySummary' => $categorySummary,
            'keyword' => $keyword ?? '',
            'selectedCategory' => $categoryId,
            'error' => $error,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $this->validatedData($request);

            $this->menuRepository->create($data);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (PDOException $exception) {
            return $this->databaseErrorResponse($exception);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', self::SAFE_ERROR_MESSAGE)->withInput();
        }

        return redirect()
            ->route('menu.index')
            ->with('sukses', 'Menu berhasil ditambahkan.');
    }

    public function edit(int $id): View|RedirectResponse
    {
        try {
            $menu = $this->menuRepository->find($id);
            $categories = $this->menuRepository->allCategories();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('menu.index')
                ->with('error', self::SAFE_ERROR_MESSAGE);
        }

        if ($menu === null) {
            return redirect()
                ->route('menu.index')
                ->with('error', 'Data menu tidak ditemukan.');
        }

        return view('menu.edit', [
            'menu' => $menu,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $menu = $this->menuRepository->find($id);

            if ($menu === null) {
                return redirect()
                    ->route('menu.index')
                    ->with('error', 'Data menu tidak ditemukan.');
            }

            $data = $this->validatedData($request, $id);

            $this->menuRepository->update($id, $data);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (PDOException $exception) {
            return $this->databaseErrorResponse($exception);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', self::SAFE_ERROR_MESSAGE)->withInput();
        }

        return redirect()
            ->route('menu.index')
            ->with('sukses', 'Menu berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $deleted = $this->menuRepository->delete($id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', self::SAFE_ERROR_MESSAGE);
        }

        if (! $deleted) {
            return redirect()
                ->route('menu.index')
                ->with('error', 'Data menu tidak ditemukan atau sudah dihapus.');
        }

        return redirect()
            ->route('menu.index')
            ->with('sukses', 'Menu berhasil dihapus.');
    }

    /**
     * @return array{kode_menu: string, nama_menu: string, barcode: string, harga: int, kategori_menu_id: int}
     *
     * @throws ValidationException
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = [
            'kode_menu' => trim(strip_tags((string) $request->input('kode_menu', ''))),
            'nama_menu' => trim(strip_tags((string) $request->input('nama_menu', ''))),
            'barcode' => trim(strip_tags((string) $request->input('barcode', ''))),
            'harga' => $request->input('harga'),
            'kategori_menu_id' => $request->input('kategori_menu_id'),
        ];

        $validator = Validator::make($data, [
            'kode_menu' => ['required', 'string', 'size:10', 'alpha_num:ascii'],
            'nama_menu' => ['required', 'string', 'min:3', 'max:100'],
            'barcode' => ['required', 'string', 'min:5', 'max:30'],
            'harga' => ['required', 'integer', 'min:1000', 'max:100000'],
            'kategori_menu_id' => ['required', 'integer', 'exists:kategori_menu,id'],
        ], [
            'kode_menu.required' => 'Kode menu wajib diisi.',
            'kode_menu.size' => 'Kode menu harus tepat 10 karakter.',
            'kode_menu.alpha_num' => 'Kode menu hanya boleh berisi huruf dan angka.',
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'nama_menu.min' => 'Nama menu minimal 3 karakter.',
            'nama_menu.max' => 'Nama menu maksimal 100 karakter.',
            'barcode.required' => 'Barcode wajib diisi.',
            'barcode.min' => 'Barcode minimal 5 karakter.',
            'barcode.max' => 'Barcode maksimal 30 karakter.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.integer' => 'Harga harus berupa bilangan bulat.',
            'harga.min' => 'Harga minimal Rp1.000.',
            'harga.max' => 'Harga maksimal Rp100.000.',
            'kategori_menu_id.required' => 'Kategori menu wajib dipilih.',
            'kategori_menu_id.integer' => 'Kategori menu tidak valid.',
            'kategori_menu_id.exists' => 'Kategori menu yang dipilih tidak tersedia.',
        ]);

        $validator->after(function ($validator) use ($data, $ignoreId): void {
            $code = (string) $data['kode_menu'];
            $barcode = (string) $data['barcode'];

            if (! $validator->errors()->has('kode_menu') && $this->menuRepository->codeExists($code, $ignoreId)) {
                $validator->errors()->add('kode_menu', 'Kode menu sudah digunakan.');
            }

            if (! $validator->errors()->has('barcode') && $this->menuRepository->barcodeExists($barcode, $ignoreId)) {
                $validator->errors()->add('barcode', 'Barcode sudah digunakan.');
            }
        });

        $validated = $validator->validate();

        return [
            'kode_menu' => $validated['kode_menu'],
            'nama_menu' => $validated['nama_menu'],
            'barcode' => $validated['barcode'],
            'harga' => (int) $validated['harga'],
            'kategori_menu_id' => (int) $validated['kategori_menu_id'],
        ];
    }

    private function databaseErrorResponse(PDOException $exception): RedirectResponse
    {
        report($exception);

        if ((string) $exception->getCode() === '23000') {
            return back()
                ->withErrors([
                    'kode_menu' => 'Kode menu atau barcode sudah digunakan.',
                    'barcode' => 'Kode menu atau barcode sudah digunakan.',
                ])
                ->withInput();
        }

        return back()->with('error', self::SAFE_ERROR_MESSAGE)->withInput();
    }
}
