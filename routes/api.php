<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReviewImageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('login-google', 'loginGoogle')->name('login.google');
});
Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('forget-password', 'forgetPassword')->name('password.forget');
    Route::post('forget-password/resend-otp', 'resendOtp')->name('password.resendOtp');
    Route::post('forget-password/otp-verification', 'otpVerification')->name('password.otpVerification');
    Route::post('forget-password/reset-password', 'resetPassword')->name('password.reset');
});

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('register/resend-otp', 'resendOtp')->name('register.resendOtp');
    Route::post('register/otp-verification', 'otpVerification')->name('register.otpVerification');
    Route::post('register/create-account', 'createAccount')->name('register.createAccount');
});

Route::controller(ProductController::class)->group(function () {
    Route::get('products', 'getAll')->name('products');
    Route::get('products/{id}', 'show')->name('products.show');
});

Route::controller(ReviewController::class)->group(function () {
    Route::get('products/{id}/reviews', 'show')->name('review.show');
});

Route::middleware('auth:api')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('me', 'me')->name('me');
        Route::post('refresh', 'refresh')->name('refresh');
        Route::post('logout', 'logout')->name('logout');
    });
    Route::controller(UserController::class)->group(function () {
        Route::put('user', 'updateUser')->name('update.user');
        Route::put('user/email', 'updateEmail')->name('update.user.email');
        Route::put('user/resend-otp', 'resendOtp')->name('update.user.resendOtp');
        Route::put('user/verifikasi-otp', 'verifikasiOtp')->name('update.user.verifikasiOtp');
        Route::put('user/change-password', 'changePassword')->name('update.user.changePassword');
    });

    Route::controller(ReviewController::class)->group(function () {
        Route::post('products/{id}/reviews', 'store')->name('review.store');
        Route::put('products/reviews/{id}', 'update')->name('review.update');
        Route::delete('products/reviews/{id}', 'destroy')->name('review.destroy');
    });

    Route::controller(ReviewImageController::class)->group(function () {
        Route::post('products/reviews/{id}/images', 'store')->name('review.image.store');
        Route::delete('products/reviews/images/{id}', 'destroy')->name('review.image.destroy');
    });

    Route::controller(MessageController::class)->group(function () {
        Route::get('messages', 'show')->name('messages.show');
        Route::get('messages/latest', 'newMessage')->name('messages.newMessage');
        Route::post('messages/{admin_id}', 'store')->name('messages.store');
        Route::put('messages/read/{admin_id}', 'read')->name('messages.read');
    });
});

Route::resource('temps', ProductController::class);

require __DIR__ . '/admin.php';
