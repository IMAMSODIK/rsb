<?php

namespace App\Http\Controllers;

use App\Models\IkuKinerjaLayanan;
use App\Models\KategoriKinerjaLayanan;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IkuLayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = IkuKinerjaLayanan::with('kategori')->orderBy('kode_iku')->get();
                return response()->json(['data' => $data]);
            }
            $kategori = KategoriKinerjaLayanan::orderBy('kode_kategori')->get();
            return view('master.iku-layanan.index', compact('kategori'));
        } catch (Exception $e) {
            Log::error('IkuLayanan index error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }
            return redirect()->back()->with('error', 'Gagal memuat halaman.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'kategori_id' => 'required|exists:kategori_kinerja_layanan,id',
                'kode_iku'    => 'required|string|max:50|unique:iku_kinerja_layanan,kode_iku',
                'uraian'      => 'required|string',
            ], [
                'kategori_id.required' => 'Kategori layanan wajib dipilih.',
                'kategori_id.exists'   => 'Kategori layanan tidak valid.',
                'kode_iku.required'    => 'Kode IKU wajib diisi.',
                'kode_iku.unique'      => 'Kode IKU sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
            ]);

            IkuKinerjaLayanan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'IKU kinerja layanan berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('IkuLayanan store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = IkuKinerjaLayanan::findOrFail($id);

            $data = $request->validate([
                'kategori_id' => 'required|exists:kategori_kinerja_layanan,id',
                'kode_iku'    => 'required|string|max:50|unique:iku_kinerja_layanan,kode_iku,' . $id,
                'uraian'      => 'required|string',
            ], [
                'kategori_id.required' => 'Kategori layanan wajib dipilih.',
                'kategori_id.exists'   => 'Kategori layanan tidak valid.',
                'kode_iku.required'    => 'Kode IKU wajib diisi.',
                'kode_iku.unique'      => 'Kode IKU sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'IKU kinerja layanan berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('IkuLayanan update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = IkuKinerjaLayanan::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'IKU kinerja layanan berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('IkuLayanan destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
