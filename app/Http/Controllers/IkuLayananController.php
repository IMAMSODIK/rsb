<?php

namespace App\Http\Controllers;

use App\Models\IkuKinerjaLayanan;
use App\Models\SasaranStrategis;
use App\Models\UnitKerja;
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
                $data = IkuKinerjaLayanan::with(['sasaranStrategis', 'unitKerja'])->orderBy('kode_iku')->get();
                return response()->json(['data' => $data]);
            }
            $sasaranStrategis = SasaranStrategis::orderBy('kode_sasaran_strategis')->get();
            $unitKerja = UnitKerja::orderBy('kode_unit')->get();
            return view('master.iku-layanan.index', compact('sasaranStrategis', 'unitKerja'));
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
                'sasaran_strategis_id' => 'required|exists:sasaran_strategis,id',
                'unit_kerja_id'       => 'required|exists:unit_kerja,id',
                'kode_iku'            => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'              => 'required|string',
            ], [
                'sasaran_strategis_id.required' => 'Sasaran strategis wajib dipilih.',
                'sasaran_strategis_id.exists'   => 'Sasaran strategis tidak valid.',
                'unit_kerja_id.required'        => 'Unit kerja wajib dipilih.',
                'unit_kerja_id.exists'          => 'Unit kerja tidak valid.',
                'kode_iku.required'             => 'Kode IKU wajib diisi.',
                'kode_iku.regex'                => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            $sasaranStrategis = SasaranStrategis::findOrFail($data['sasaran_strategis_id']);
            $kodeLengkap = $sasaranStrategis->kode_sasaran_strategis . '.' . trim($data['kode_iku']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_iku' => 'Kode IKU maksimal 50 karakter.',
                ]);
            }
            if (IkuKinerjaLayanan::where('kode_iku', $kodeLengkap)->exists()) {
                throw ValidationException::withMessages([
                    'kode_iku' => 'Kode IKU sudah digunakan.',
                ]);
            }
            $data['kode_iku'] = $kodeLengkap;

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
                'sasaran_strategis_id' => 'required|exists:sasaran_strategis,id',
                'unit_kerja_id'       => 'required|exists:unit_kerja,id',
                'kode_iku'            => 'required|string|max:49|regex:/^[^.]+$/',
                'uraian'              => 'required|string',
            ], [
                'sasaran_strategis_id.required' => 'Sasaran strategis wajib dipilih.',
                'sasaran_strategis_id.exists'   => 'Sasaran strategis tidak valid.',
                'unit_kerja_id.required'        => 'Unit kerja wajib dipilih.',
                'unit_kerja_id.exists'          => 'Unit kerja tidak valid.',
                'kode_iku.required'             => 'Kode IKU wajib diisi.',
                'kode_iku.regex'                => 'Kode tambahan tidak boleh mengandung titik.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            $sasaranStrategis = SasaranStrategis::findOrFail($data['sasaran_strategis_id']);
            $kodeLengkap = $sasaranStrategis->kode_sasaran_strategis . '.' . trim($data['kode_iku']);
            if (strlen($kodeLengkap) > 50) {
                throw ValidationException::withMessages([
                    'kode_iku' => 'Kode IKU maksimal 50 karakter.',
                ]);
            }
            if (IkuKinerjaLayanan::where('kode_iku', $kodeLengkap)
                ->where('id', '!=', $id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'kode_iku' => 'Kode IKU sudah digunakan.',
                ]);
            }
            $data['kode_iku'] = $kodeLengkap;

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
