<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends BaseController
{
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()]
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $user = Admin::find(Auth::guard('admin')->user()->id);
        if ($user) {
            if (Hash::check($validateData['old_password'], $user->password)) {
                $user->update([
                    'password' => $validateData['password']
                ]);
            } else {
                return $this->sendError('Kesalahan validasi', ["password" => ["Kata sandi lama tidak sesuai"]], 400);
            }
        } else {
            return $this->sendError('User tidak ditemukan', ["errors" => ["User tidak ditemukan"]]);
        }

        $success['user'] = $user;
        return $this->sendResponse('Berhasil ubah kata sandi', $success);
    }
}
