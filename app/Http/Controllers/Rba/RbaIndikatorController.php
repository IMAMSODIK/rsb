<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaIndikator;
use App\Models\Rba\RbaKategori;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaIndikatorController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history
    ) {}

    public function index(Request $request)
    {
        RbaModuleGuard::ensureInstalled();
        try {
            if ($request->ajax()) {
                $data = RbaIndikator::with('kategori.jenis')->orderBy('kode_indikator')->get();

                return response()->json(['data' => $data]);
            }

            $kategori = RbaKategori::with('jenis')->orderBy('kode_kategori')->get();

            return view('rba.indikator.index', compact('kategori'));
        } catch (Exception $e) {
            Log::error('RbaIndikator index error: '.$e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Indikator RBA: '.$e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'kategori_id' => 'required|exists:rba_kategori,id',
                'kode_indikator' => 'required|string|max:50|unique:rba_indikator,kode_indikator',
                'uraian' => 'required|string',
            ], [
                'kategori_id.required' => 'Kategori RBA wajib dipilih.',
                'kategori_id.exists' => 'Kategori RBA tidak valid.',
                'kode_indikator.required' => 'Kode indikator wajib diisi.',
                'kode_indikator.unique' => 'Kode indikator sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $item = RbaIndikator::create($data);
            $this->history->record('INSERT', 'rba_indikator', $item->id, null, $item);

            return response()->json([
                'success' => true,
                'message' => 'Indikator RBA berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaIndikator store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = RbaIndikator::findOrFail($id);
            $data = $request->validate([
                'kategori_id' => 'required|exists:rba_kategori,id',
                'kode_indikator' => 'required|string|max:50|unique:rba_indikator,kode_indikator,'.$id,
                'uraian' => 'required|string',
            ], [
                'kategori_id.required' => 'Kategori RBA wajib dipilih.',
                'kategori_id.exists' => 'Kategori RBA tidak valid.',
                'kode_indikator.required' => 'Kode indikator wajib diisi.',
                'kode_indikator.unique' => 'Kode indikator sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $sebelum = $item->getAttributes();
            $item->update($data);
            $this->history->record('UPDATE', 'rba_indikator', $item->id, $sebelum, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Indikator RBA berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaIndikator update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = RbaIndikator::findOrFail($id);
            $sebelum = $item->getAttributes();
            $item->delete();
            $this->history->record('DELETE', 'rba_indikator', (int) $id, $sebelum, null);

            return response()->json([
                'success' => true,
                'message' => 'Indikator RBA berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('RbaIndikator destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
