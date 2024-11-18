<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->group(function () {
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::controller(LoginController::class)->middleware('auth:admin')->group(function () {
        Route::get('me', 'me')->name('me');
        Route::post('refresh', 'refresh')->name('refresh');
        Route::post('logout', 'logout')->name('register');
        Route::controller(ProductController::class)->group(function () {
            Route::get('products', 'getAll')->name('products');
        });
    });

    Route::controller(AdminController::class)->middleware('auth:admin')->group(function () {
        Route::put('change-password', 'changePassword')->name('changePassword');
    });
});

Route::middleware('auth:admin')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::post('products', 'store')->name('products.store');
        Route::put('products/{id}', 'update')->name('products.update');
        Route::delete('products/{id}', 'destroy')->name('product.destroy');
    });

    Route::controller(ImageController::class)->group(function () {
        Route::post('images', 'store')->name('images.store');
        Route::put('images/{id}', 'update')->name('images.update');
        Route::delete('images/{id}', 'destroy')->name('images.destroy');
    });
});
