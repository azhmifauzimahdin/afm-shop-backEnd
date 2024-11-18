<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ReviewImageController extends BaseController
{
    public function store(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $review = Review::find($id);
        if ($review) {
            if ($review['user_id'] !== Auth::user()->id)  return $this->sendError('Gagal tambah foto review', ['error' => 'User tidak memiliki akses untuk tambah foto review'], 401);


            $reviewImages = [];
            if ($request->file('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('images/reviews/', 'public');
                    $images[] = ['review_id' => $review['id'], 'title' => $image->getClientOriginalName(), 'name' => basename($path), 'size' => $image->getSize()];
                }

                foreach ($images as $imageData) {
                    $image = ReviewImage::create($imageData);
                    $reviewImages[] = $image;
                }
            }

            $success['images'] = $reviewImages;

            return $this->sendResponse('Berhasil tambah foto ulasan', $success);
        } else {
            return $this->sendError('Gagal tambah foto ulasan', ['error' => 'Ulasan tidak ditemukan']);
        }
        return $this->sendFail();
    }


    public function destroy($id): JsonResponse
    {
        $image = ReviewImage::find($id);
        if ($image) {
            if ($image->review['user_id'] !== Auth::user()->id)  return $this->sendError('Gagal hapus foto review', ['error' => 'User tidak memiliki akses untuk hapus foto review'], 401);
            $succes = $image;
            $file_path = public_path('storage/images/reviews/' . $image->name);
            if (File::exists($file_path)) {
                File::delete($file_path);
            }
            $image->delete();
            return $this->sendResponse('Berhasil hapus foto review', ['image' => $succes]);
        } else {
            return $this->sendError('Gagal hapus foto review', ['error' => 'Foto tidak ditemukan']);
        }
        return $this->sendFail();
    }
}
