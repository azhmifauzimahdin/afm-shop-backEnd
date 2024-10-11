<?php

use App\Http\Controllers\Api\OauthController;
use Illuminate\Support\Facades\Route;

Route::controller(OauthController::class)->group(function () {
    Route::get('api/oauth/google', 'redirectToProvider')->name('oauth.google');
    Route::get('api/oauth/google/callback', 'handleProviderCallback')->name('oauth.google.callback');
});
