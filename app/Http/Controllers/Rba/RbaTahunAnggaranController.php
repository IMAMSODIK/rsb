<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaTahunAnggaran;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaTahunAnggaranController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history
    ) {}

    public function index(Request $request)
    {
        RbaModuleGuard::ensureInstalled();
        try {
            if ($request->ajax()) {
                $data = RbaTahunAnggaran::orderBy('tahun', 'desc')->get();

                return response()->json(['data' => $data]);
            }

            return view('rba.tahun.index');
        } catch (Exception $e) {
            Log::error('RbaTahun index error: '.$e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Tahun Anggaran RBA: '.$e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'tahun' => 'required|integer|min:2000|max:2100|unique:rba_tahun_anggaran,tahun',
                'status' => 'required|in:OPEN,LOCKED,CLOSED',
                'is_current' => 'sometimes|boolean',
            ], [
                'tahun.required' => 'Tahun wajib diisi.',
                'tahun.unique' => 'Tahun sudah terdaftar.',
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid.',
            ]);
            $data['is_current'] = $request->boolean('is_current');

            DB::beginTransaction();
            if ($data['is_current']) {
                RbaTahunAnggaran::where('is_current', true)->update(['is_current' => false]);
            }
            $item = RbaTahunAnggaran::create($data);
            DB::commit();

            $this->history->record('INSERT', 'rba_tahun_anggaran', $item->id, null, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Tahun anggaran berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RbaTahun store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = RbaTahunAnggaran::findOrFail($id);
            $data = $request->validate([
                'tahun' => 'required|integer|min:2000|max:2100|unique:rba_tahun_anggaran,tahun,'.$id,
                'status' => 'required|in:OPEN,LOCKED,CLOSED',
                'is_current' => 'sometimes|boolean',
            ], [
                'tahun.required' => 'Tahun wajib diisi.',
                'tahun.unique' => 'Tahun sudah terdaftar.',
                'status.required' => 'Status wajib dipilih.',
                'status.in' => 'Status tidak valid.',
            ]);
            $data['is_current'] = $request->boolean('is_current');

            DB::beginTransaction();
            if ($data['is_current']) {
                RbaTahunAnggaran::where('is_current', true)->where('id', '!=', $id)->update(['is_current' => false]);
            }
            $sebelum = $item->getAttributes();
            $item->update($data);
            DB::commit();

            $this->history->record('UPDATE', 'rba_tahun_anggaran', $item->id, $sebelum, $item->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Tahun anggaran berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RbaTahun update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = RbaTahunAnggaran::findOrFail($id);
            $sebelum = $item->getAttributes();
            $item->delete();
            $this->history->record('DELETE', 'rba_tahun_anggaran', (int) $id, $sebelum, null);

            return response()->json([
                'success' => true,
                'message' => 'Tahun anggaran berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('RbaTahun destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }

    public function current(): JsonResponse
    {
        $tahun = RbaTahunAnggaran::where('is_current', true)->first()
            ?? RbaTahunAnggaran::orderBy('tahun', 'desc')->first();

        return response()->json(['success' => true, 'data' => $tahun]);
    }
}
