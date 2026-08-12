<?php

namespace App\Http\Controllers;

use App\Models\SasaranStrategis;
use App\Models\SasaranProgram;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SasaranStrategisController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = SasaranStrategis::with('sasaranProgram')->orderBy('kode_sasaran_strategis')->get();
                return response()->json(['data' => $data]);
            }
            $sasaranProgram = SasaranProgram::orderBy('kode_sasaran_program')->get();
            return view('master.sasaran-strategis.index', compact('sasaranProgram'));
        } catch (Exception $e) {
            Log::error('SasaranStrategis index error: ' . $e->getMessage());
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
                'sasaran_program_id'    => 'required|exists:sasaran_program,id',
                'kode_sasaran_strategis' => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'                 => 'required|string',
            ], [
                'sasaran_program_id.required' => 'Sasaran program wajib dipilih.',
                'sasaran_program_id.exists'   => 'Sasaran program tidak valid.',
                'kode_sasaran_strategis.required' => 'Kode sasaran strategis wajib diisi.',
                'kode_sasaran_strategis.regex'    => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'                 => 'Uraian wajib diisi.',
            ]);

            $sasaranProgram = SasaranProgram::findOrFail($data['sasaran_program_id']);
            $kodeLengkap = $sasaranProgram->kode_sasaran_program . '.' . trim($data['kode_sasaran_strategis']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_sasaran_strategis' => 'Kode sasaran strategis maksimal 50 karakter.',
                ]);
            }
            if (SasaranStrategis::where('kode_sasaran_strategis', $kodeLengkap)->exists()) {
                throw ValidationException::withMessages([
                    'kode_sasaran_strategis' => 'Kode sasaran strategis sudah digunakan.',
                ]);
            }
            $data['kode_sasaran_strategis'] = $kodeLengkap;

            SasaranStrategis::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranStrategis store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = SasaranStrategis::findOrFail($id);

            $data = $request->validate([
                'sasaran_program_id'    => 'required|exists:sasaran_program,id',
                'kode_sasaran_strategis' => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'                 => 'required|string',
            ], [
                'sasaran_program_id.required' => 'Sasaran program wajib dipilih.',
                'sasaran_program_id.exists'   => 'Sasaran program tidak valid.',
                'kode_sasaran_strategis.required' => 'Kode sasaran strategis wajib diisi.',
                'kode_sasaran_strategis.regex'    => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'                 => 'Uraian wajib diisi.',
            ]);

            $sasaranProgram = SasaranProgram::findOrFail($data['sasaran_program_id']);
            $kodeLengkap = $sasaranProgram->kode_sasaran_program . '.' . trim($data['kode_sasaran_strategis']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_sasaran_strategis' => 'Kode sasaran strategis maksimal 50 karakter.',
                ]);
            }
            if (SasaranStrategis::where('kode_sasaran_strategis', $kodeLengkap)
                ->where('id', '!=', $id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'kode_sasaran_strategis' => 'Kode sasaran strategis sudah digunakan.',
                ]);
            }
            $data['kode_sasaran_strategis'] = $kodeLengkap;

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranStrategis update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = SasaranStrategis::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('SasaranStrategis destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
