<?php

namespace App\Http\Controllers;

use App\Models\SliderItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SliderItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = SliderItem::orderBy('urutan')->orderBy('id')->get();

                return response()->json(['data' => $data]);
            }

            return view('master.settings.index');
        } catch (Exception $e) {
            Log::error('SliderItem index error: '.$e->getMessage());
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
                'judul' => 'nullable|string|max:255',
                'gambar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'urutan' => 'nullable|integer|min:0',
                'is_active' => 'sometimes|boolean',
            ], [
                'gambar.required' => 'Gambar wajib diunggah.',
                'gambar.image' => 'File harus berupa gambar.',
                'gambar.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
                'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
                'urutan.integer' => 'Urutan harus berupa angka.',
            ]);

            $path = $request->file('gambar')->store('slider', 'public');
            $data['gambar'] = $path;
            $data['is_active'] = $request->boolean('is_active', true);

            SliderItem::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SliderItem store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = SliderItem::findOrFail($id);

            $data = $request->validate([
                'judul' => 'nullable|string|max:255',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'urutan' => 'nullable|integer|min:0',
                'is_active' => 'sometimes|boolean',
            ], [
                'gambar.image' => 'File harus berupa gambar.',
                'gambar.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
                'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
                'urutan.integer' => 'Urutan harus berupa angka.',
            ]);

            // Ganti gambar jika ada file baru
            if ($request->hasFile('gambar')) {
                // Hapus file lama jika bukan URL eksternal
                if (! $item->isExternalUrl() && Storage::disk('public')->exists($item->gambar)) {
                    Storage::disk('public')->delete($item->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('slider', 'public');
            } else {
                unset($data['gambar']);
            }

            // Pastikan is_active selalu ada (checkbox tidak ter-submit saat unchecked)
            $data['is_active'] = $request->boolean('is_active', false);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SliderItem update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = SliderItem::findOrFail($id);

            // Hapus file lokal jika bukan URL eksternal
            if (! $item->isExternalUrl() && Storage::disk('public')->exists($item->gambar)) {
                Storage::disk('public')->delete($item->gambar);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('SliderItem destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data.',
            ], 500);
        }
    }
}
