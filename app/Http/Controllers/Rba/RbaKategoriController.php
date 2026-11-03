<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaKategori;
use App\Models\Rba\RbaJenis;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaKategoriController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history
    ) {}

    public function index(Request $request)
    {
        RbaModuleGuard::ensureInstalled();
        try {
            if ($request->ajax()) {
                $data = RbaKategori::with('jenis')->orderBy('kode_kategori')->get();

                return response()->json(['data' => $data]);
            }

            $jenis = RbaJenis::orderBy('kode_jenis')->get();

            return view('rba.kategori.index', compact('jenis'));
        } catch (Exception $e) {
            Log::error('RbaKategori index error: '.$e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Kategori RBA: '.$e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'jenis_id' => 'required|exists:rba_jenis,id',
                'kode_kategori' => 'required|string|max:50|unique:rba_kategori,kode_kategori',
                'uraian' => 'required|string',
            ], [
                'jenis_id.required' => 'Jenis RBA wajib dipilih.',
                'jenis_id.exists' => 'Jenis RBA tidak valid.',
                'kode_kategori.required' => 'Kode kategori wajib diisi.',
                'kode_kategori.unique' => 'Kode kategori sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $item = RbaKategori::create($data);
            $this->history->record('INSERT', 'rba_kategori', $item->id, null, $item);

            return response()->json([
                'success' => true,
                'message' => 'Kategori RBA berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaKategori store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = RbaKategori::findOrFail($id);
            $data = $request->validate([
                'jenis_id' => 'required|exists:rba_jenis,id',
                'kode_kategori' => 'required|string|max:50|unique:rba_kategori,kode_kategori,'.$id,
                'uraian' => 'required|string',
            ], [
                'jenis_id.required' => 'Jenis RBA wajib dipilih.',
                'jenis_id.exists' => 'Jenis RBA tidak valid.',
                'kode_kategori.required' => 'Kode kategori wajib diisi.',
                'kode_kategori.unique' => 'Kode kategori sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $sebelum = $item->getAttributes();
            $item->update($data);
            $this->history->record('UPDATE', 'rba_kategori', $item->id, $sebelum, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Kategori RBA berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaKategori update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = RbaKategori::findOrFail($id);
            $sebelum = $item->getAttributes();
            $item->delete();
            $this->history->record('DELETE', 'rba_kategori', (int) $id, $sebelum, null);

            return response()->json([
                'success' => true,
                'message' => 'Kategori RBA berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('RbaKategori destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
