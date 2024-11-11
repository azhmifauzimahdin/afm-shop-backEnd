<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function getAll(): JsonResponse
    {
        return $this->sendResponse('Berhasil ambil data produk', ['products' => Product::filter(request(['search']))->orderBy('updated_at', 'desc')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'discount' => ['numeric', 'max:100'],
            'images' => ['array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $product = Product::create($validateData);

        if ($product) {
            $productImages = [];
            if ($request->file('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('images/products/', 'public');
                    $images[] = ['product_id' => $product['id'], 'name' => basename($path), 'size' => $image->getSize()];
                }

                foreach ($images as $imageData) {
                    $image = Image::create($imageData);
                    $productImages[] = $image;
                }
            }

            $success['product'] = $product;
            $product['images'] = $productImages;

            if ($product) {
                return $this->sendResponse('Berhasil tambah data produk', $success);
            }
        }

        return $this->sendFail();
    }

    public function show($id): JsonResponse
    {
        $product = Product::find($id);
        if ($product) {
            return $this->sendResponse('Berhasil ambil data produk', ['product' => $product]);
        } else {
            return $this->sendError('Produk tidak ditemukan');
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'discount' => ['numeric', 'max:100'],
            'images' => ['array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $product = Product::find($id);

        if ($product->update($validateData)) {
            if ($request->file('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('images/products/', 'public');
                    $images[] = ['product_id' => $product['id'], 'name' => basename($path), 'size' => $image->getSize()];
                }

                foreach ($images as $imageData) {
                    $image = Image::create($imageData);
                }
            }

            $success['product'] = Product::find($id);
            return $this->sendResponse('Berhasil ubah produk', $success);
        };
        return $this->sendFail();
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);
        if ($product) {
            $succes = $product;
            if ($product->images) {
                foreach ($product->images as $image) {
                    $file_path = public_path('storage/images/products/' . $image->name);
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }
                }
            }
            $product->delete();
            return $this->sendResponse('Berhasil hapus data produk', ['product' => $succes]);
        } else {
            return $this->sendError('Produk tidak ditemukan');
        }
    }
}
