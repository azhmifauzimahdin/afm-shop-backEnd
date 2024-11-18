<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'exists:products,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1000']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        if ($request->hasFile('image')) {
            $fileImage = $request->file('image');
            $path = $fileImage->store('images/products/', 'public');

            $image = Image::create([
                'product_id' => $validateData['product_id'],
                'title' => $fileImage->getClientOriginalName(),
                'name' => basename($path),
                'size' => $fileImage->getSize()
            ]);

            $success['image'] = $image;
            if ($image) {
                return $this->sendResponse('Berhasil tambah data foto', $success);
            }
        }
        return $this->sendFail();
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1000']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $image = Image::find($id);
        if ($image) {
            if ($request->hasFile('image')) {
                $fileImage = $request->file('image');
                $path = $fileImage->store('images/products/', 'public');
                if ($image->name) {
                    $file_path = public_path('storage/images/products/' . $image->name);
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }
                }
                $image->update([
                    'title' => $fileImage->getClientOriginalName(),
                    'name' => basename($path),
                    'size' => $fileImage->getSize()
                ]);
            } else {
                return $this->sendFail();
            }
        } else {
            return $this->sendError('Gagal update foto', ['error' => 'Foto tidak ditemukan']);
        }

        $success['image'] = $image;
        return $this->sendResponse('Berhasil ubah foto', $success);
    }

    public function destroy($id): JsonResponse
    {
        $image = Image::find($id);
        if ($image) {
            $succes = $image;
            $file_path = public_path('storage/images/products/' . $image->name);
            if (File::exists($file_path)) {
                File::delete($file_path);
            }
            $image->delete();
            return $this->sendResponse('Berhasil hapus foto', ['image' => $succes]);
        } else {
            return $this->sendError('Gagal hapus foto', ['error' => 'Foto tidak ditemukan']);
        }
    }
}
