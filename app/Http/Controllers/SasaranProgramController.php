<?php

namespace App\Http\Controllers;

use App\Models\PeriodeRsb;
use App\Models\SasaranProgram;
use App\Models\TujuanRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SasaranProgramController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $periodeId = $request->query('periode_rsb_id');
                $data = SasaranProgram::with('tujuan')
                    ->when($periodeId, fn ($query) => $query->whereHas('tujuan', fn ($q) => $q->where(function ($sub) use ($periodeId) {
                        $sub->whereNull('periode_rsb_id')->orWhere('periode_rsb_id', $periodeId);
                    })))
                    ->orderBy('kode_sasaran_program')->get();
                return response()->json(['data' => $data]);
            }
            $tujuan = TujuanRsb::orderBy('kode_tujuan')->get();
            $periode = PeriodeRsb::orderBy('tahun_awal')->get();
            $periodeAktif = $periode->firstWhere('is_active', true);
            return view('master.sasaran-program.index', compact('tujuan', 'periode', 'periodeAktif'));
        } catch (Exception $e) {
            Log::error('SasaranProgram index error: ' . $e->getMessage());
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
                'tujuan_id'            => 'required|exists:tujuan_rsb,id',
                'kode_sasaran_program' => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'               => 'required|string',
            ], [
                'tujuan_id.required'            => 'Tujuan RSB wajib dipilih.',
                'tujuan_id.exists'              => 'Tujuan RSB tidak valid.',
                'kode_sasaran_program.required' => 'Kode sasaran program wajib diisi.',
                'kode_sasaran_program.regex'    => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            $tujuan = TujuanRsb::findOrFail($data['tujuan_id']);
            $kodeLengkap = $tujuan->kode_tujuan . '.' . trim($data['kode_sasaran_program']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_sasaran_program' => 'Kode sasaran program maksimal 50 karakter.',
                ]);
            }
            if (SasaranProgram::where('kode_sasaran_program', $kodeLengkap)->exists()) {
                throw ValidationException::withMessages([
                    'kode_sasaran_program' => 'Kode sasaran program sudah digunakan.',
                ]);
            }
            $data['kode_sasaran_program'] = $kodeLengkap;

            SasaranProgram::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranProgram store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = SasaranProgram::findOrFail($id);

            $data = $request->validate([
                'tujuan_id'            => 'required|exists:tujuan_rsb,id',
                'kode_sasaran_program' => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'               => 'required|string',
            ], [
                'tujuan_id.required'            => 'Tujuan RSB wajib dipilih.',
                'tujuan_id.exists'              => 'Tujuan RSB tidak valid.',
                'kode_sasaran_program.required' => 'Kode sasaran program wajib diisi.',
                'kode_sasaran_program.regex'    => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            $tujuan = TujuanRsb::findOrFail($data['tujuan_id']);
            $kodeLengkap = $tujuan->kode_tujuan . '.' . trim($data['kode_sasaran_program']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_sasaran_program' => 'Kode sasaran program maksimal 50 karakter.',
                ]);
            }
            if (SasaranProgram::where('kode_sasaran_program', $kodeLengkap)
                ->where('id', '!=', $id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'kode_sasaran_program' => 'Kode sasaran program sudah digunakan.',
                ]);
            }
            $data['kode_sasaran_program'] = $kodeLengkap;

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranProgram update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = SasaranProgram::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('SasaranProgram destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
