<?php

namespace App\Http\Controllers;

use App\Models\DokumenRenstra;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DokumenRenstraController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = DokumenRenstra::orderBy('tahun', 'desc')->orderBy('id')->get();

                return response()->json(['data' => $data]);
            }

            return view('master.dokumen-renstra.index');
        } catch (Exception $e) {
            Log::error('DokumenRenstra index error: '.$e->getMessage());
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
                'tahun' => 'required|integer|min:2000|max:2100',
                'uraian' => 'required|string|max:1000',
                'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            ], [
                'tahun.required' => 'Tahun wajib diisi.',
                'tahun.integer' => 'Tahun harus berupa angka.',
                'uraian.required' => 'Uraian wajib diisi.',
                'file.required' => 'File dokumen wajib diunggah.',
                'file.mimes' => 'File harus berformat pdf, doc, docx, xls, atau xlsx.',
                'file.max' => 'Ukuran file maksimal 10 MB.',
            ]);

            $file = $request->file('file');
            $path = $file->store('dokumen-renstra', 'public');

            DokumenRenstra::create([
                'tahun' => $data['tahun'],
                'uraian' => $data['uraian'],
                'nama_asli' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'ukuran' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen renstra berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('DokumenRenstra store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = DokumenRenstra::findOrFail($id);

            $data = $request->validate([
                'tahun' => 'required|integer|min:2000|max:2100',
                'uraian' => 'required|string|max:1000',
                'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            ], [
                'tahun.required' => 'Tahun wajib diisi.',
                'tahun.integer' => 'Tahun harus berupa angka.',
                'uraian.required' => 'Uraian wajib diisi.',
                'file.mimes' => 'File harus berformat pdf, doc, docx, xls, atau xlsx.',
                'file.max' => 'Ukuran file maksimal 10 MB.',
            ]);

            // Ganti file jika ada file baru
            if ($request->hasFile('file')) {
                if (Storage::disk('public')->exists($item->path)) {
                    Storage::disk('public')->delete($item->path);
                }
                $file = $request->file('file');
                $data['path'] = $file->store('dokumen-renstra', 'public');
                $data['nama_asli'] = $file->getClientOriginalName();
                $data['mime_type'] = $file->getClientMimeType();
                $data['ukuran'] = $file->getSize();
            }
            unset($data['file']);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen renstra berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('DokumenRenstra update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = DokumenRenstra::findOrFail($id);

            if (Storage::disk('public')->exists($item->path)) {
                Storage::disk('public')->delete($item->path);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen renstra berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('DokumenRenstra destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data.',
            ], 500);
        }
    }
}
