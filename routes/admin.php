<?php

use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->group(function () {
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::controller(LoginController::class)->middleware('auth:admin')->group(function () {
        Route::get('me', 'me')->name('me');
        Route::post('refresh', 'refresh')->name('refresh');
        Route::post('logout', 'logout')->name('register');
    });
});
