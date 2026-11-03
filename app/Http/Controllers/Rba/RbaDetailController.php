<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaDetail;
use App\Models\Rba\RbaIndikator;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaDetailController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history
    ) {}

    public function index(Request $request)
    {
        RbaModuleGuard::ensureInstalled();
        try {
            if ($request->ajax()) {
                $data = RbaDetail::with('indikator.kategori.jenis')->orderBy('kode_detail')->get();

                return response()->json(['data' => $data]);
            }

            $indikator = RbaIndikator::with('kategori.jenis')->orderBy('kode_indikator')->get();

            return view('rba.detail.index', compact('indikator'));
        } catch (Exception $e) {
            Log::error('RbaDetail index error: '.$e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Detail RBA: '.$e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'indikator_id' => 'required|exists:rba_indikator,id',
                'kode_detail' => 'required|string|max:50|unique:rba_detail,kode_detail',
                'uraian' => 'required|string',
            ], [
                'indikator_id.required' => 'Indikator RBA wajib dipilih.',
                'indikator_id.exists' => 'Indikator RBA tidak valid.',
                'kode_detail.required' => 'Kode detail wajib diisi.',
                'kode_detail.unique' => 'Kode detail sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $item = RbaDetail::create($data);
            $this->history->record('INSERT', 'rba_detail', $item->id, null, $item);

            return response()->json([
                'success' => true,
                'message' => 'Detail RBA berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaDetail store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = RbaDetail::findOrFail($id);
            $data = $request->validate([
                'indikator_id' => 'required|exists:rba_indikator,id',
                'kode_detail' => 'required|string|max:50|unique:rba_detail,kode_detail,'.$id,
                'uraian' => 'required|string',
            ], [
                'indikator_id.required' => 'Indikator RBA wajib dipilih.',
                'indikator_id.exists' => 'Indikator RBA tidak valid.',
                'kode_detail.required' => 'Kode detail wajib diisi.',
                'kode_detail.unique' => 'Kode detail sudah digunakan.',
                'uraian.required' => 'Uraian wajib diisi.',
            ]);

            $sebelum = $item->getAttributes();
            $item->update($data);
            $this->history->record('UPDATE', 'rba_detail', $item->id, $sebelum, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Detail RBA berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaDetail update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = RbaDetail::findOrFail($id);
            $sebelum = $item->getAttributes();
            $item->delete();
            $this->history->record('DELETE', 'rba_detail', (int) $id, $sebelum, null);

            return response()->json([
                'success' => true,
                'message' => 'Detail RBA berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('RbaDetail destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
