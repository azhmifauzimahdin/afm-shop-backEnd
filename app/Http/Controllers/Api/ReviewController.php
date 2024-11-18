<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ReviewController extends BaseController
{
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return $this->sendError('Gagal menampilkan data review', ['error' => 'Produk tidak ditemukan']);
        return $this->sendResponse('Berhasil ambil data review', ['reviews' => Review::where('product_id', $id)->get()]);
    }

    public function store(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) return $this->sendError('Gagal menampilkan data review', ['error' => 'Produk tidak ditemukan']);

        $validator = Validator::make($request->all(), [
            'review' => ['required', 'string'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'images' => ['array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $validateData['user_id'] = Auth::user()->id;
        $validateData['product_id'] = $id;
        $review = Review::create($validateData);

        if ($review) {
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

            $success['review'] = $review;
            $review['images'] = $reviewImages;

            return $this->sendResponse('Berhasil tambah ulasan', $success);
        }

        return $this->sendFail();
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review' => ['required', 'string'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'images' => ['array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $review = Review::find($id);

        if ($review) {
            if ($review['user_id'] === Auth::user()->id) {
                if ($review->update($validateData)) {
                    if ($request->file('images')) {
                        $images = [];
                        foreach ($request->file('images') as $image) {
                            $path = $image->store('images/reviews/', 'public');
                            $images[] = ['review_id' => $review['id'], 'title' => $image->getClientOriginalName(), 'name' => basename($path), 'size' => $image->getSize()];
                        }

                        foreach ($images as $imageData) {
                            $image = ReviewImage::create($imageData);
                        }
                    }

                    $success['review'] = Review::find($id);
                    return $this->sendResponse('Berhasil ubah ulasan', $success);
                };
            } else {
                return $this->sendError('Gagal ubah ulasan', ['error' => 'User tidak memiliki akses untuk mengubah ulasan'], 401);
            }
        } else {
            return $this->sendError('Gagal ubah ulasan', ['error' => 'Ulasan tidak ditemukan']);
        }
        return $this->sendFail();
    }

    public function destroy($id): JsonResponse
    {
        $review = Review::find($id);
        if ($review) {
            if ($review['user_id'] === Auth::user()->id) {

                $succes = $review;
                if ($review->images) {
                    foreach ($review->images as $image) {
                        $file_path = public_path('storage/images/reviews/' . $image->name);
                        if (File::exists($file_path)) {
                            File::delete($file_path);
                        }
                    }
                }
                $review->delete();
                return $this->sendResponse('Berhasil hapus ulasan', ['review' => $succes]);
            } else {
                return $this->sendError('Gagal hapus ulasan', ['error' => 'User tidak memiliki akses untuk menghapus ulasan'], 401);
            }
        } else {
            return $this->sendError('Gagal hapus ulasan', ['error' => 'Ulasan tidak ditemukan']);
        }
    }
}
