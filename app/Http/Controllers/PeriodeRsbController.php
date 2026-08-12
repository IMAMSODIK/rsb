<?php

namespace App\Http\Controllers;

use App\Models\PeriodeRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PeriodeRsbController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = PeriodeRsb::orderBy('tahun_awal', 'desc')->get();
                return response()->json(['data' => $data]);
            }
            return view('master.periode-rsb.index');
        } catch (Exception $e) {
            Log::error('PeriodeRsb index error: ' . $e->getMessage());
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
                'tahun_awal' => 'required|integer|min:2000|max:2100',
                'tahun_akhir' => 'required|integer|min:2000|max:2100|gte:tahun_awal',
                'is_active' => 'sometimes|boolean',
            ], [
                'tahun_awal.required'  => 'Tahun awal wajib diisi.',
                'tahun_akhir.required' => 'Tahun akhir wajib diisi.',
                'tahun_akhir.gte'      => 'Tahun akhir harus lebih besar atau sama dengan tahun awal.',
            ]);

            if ((int) $data['tahun_akhir'] - (int) $data['tahun_awal'] !== 4
                || (int) $data['tahun_awal'] % 5 !== 0) {
                throw ValidationException::withMessages([
                    'tahun_akhir' => 'Periode harus mengikuti blok 5 tahunan, contoh 2025 - 2029.',
                ]);
            }

            $data['is_active'] = $request->boolean('is_active');

            DB::beginTransaction();
            // Nonaktifkan periode lain jika ini diset aktif
            if ($data['is_active']) {
                PeriodeRsb::where('is_active', true)->update(['is_active' => false]);
            }
            PeriodeRsb::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periode RSB berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PeriodeRsb store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = PeriodeRsb::findOrFail($id);

            $data = $request->validate([
                'tahun_awal'  => 'required|integer|min:2000|max:2100',
                'tahun_akhir' => 'required|integer|min:2000|max:2100|gte:tahun_awal',
                'is_active'   => 'sometimes|boolean',
            ], [
                'tahun_awal.required'  => 'Tahun awal wajib diisi.',
                'tahun_akhir.required' => 'Tahun akhir wajib diisi.',
                'tahun_akhir.gte'      => 'Tahun akhir harus lebih besar atau sama dengan tahun awal.',
            ]);

            if ((int) $data['tahun_akhir'] - (int) $data['tahun_awal'] !== 4
                || (int) $data['tahun_awal'] % 5 !== 0) {
                throw ValidationException::withMessages([
                    'tahun_akhir' => 'Periode harus mengikuti blok 5 tahunan, contoh 2025 - 2029.',
                ]);
            }

            $data['is_active'] = $request->boolean('is_active');

            DB::beginTransaction();
            if ($data['is_active']) {
                PeriodeRsb::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
            }
            $item->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periode RSB berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PeriodeRsb update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = PeriodeRsb::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periode RSB berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('PeriodeRsb destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data.',
            ], 500);
        }
    }
}
