<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaJenis;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaJenisController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history
    ) {}

    public function index(Request $request)
    {
        RbaModuleGuard::ensureInstalled();
        try {
            if ($request->ajax()) {
                $data = RbaJenis::orderBy('kode_jenis')->get();

                return response()->json(['data' => $data]);
            }

            return view('rba.jenis.index');
        } catch (Exception $e) {
            Log::error('RbaJenis index error: '.$e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Jenis RBA: '.$e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'kode_jenis' => 'required|string|max:50|unique:rba_jenis,kode_jenis',
                'uraian' => 'required|string',
            ], [
                'kode_jenis.required' => 'Kode jenis wajib diisi.',
                'kode_jenis.unique' => 'Kode jenis sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $item = RbaJenis::create($data);
            $this->history->record('INSERT', 'rba_jenis', $item->id, null, $item);

            return response()->json([
                'success' => true,
                'message' => 'Jenis RBA berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaJenis store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = RbaJenis::findOrFail($id);
            $data = $request->validate([
                'kode_jenis' => 'required|string|max:50|unique:rba_jenis,kode_jenis,'.$id,
                'uraian' => 'required|string',
            ], [
                'kode_jenis.required' => 'Kode jenis wajib diisi.',
                'kode_jenis.unique' => 'Kode jenis sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $sebelum = $item->getAttributes();
            $item->update($data);
            $this->history->record('UPDATE', 'rba_jenis', $item->id, $sebelum, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Jenis RBA berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaJenis update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = RbaJenis::findOrFail($id);
            $sebelum = $item->getAttributes();
            $item->delete();
            $this->history->record('DELETE', 'rba_jenis', (int) $id, $sebelum, null);

            return response()->json([
                'success' => true,
                'message' => 'Jenis RBA berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('RbaJenis destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
