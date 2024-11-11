<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // 'email' => ['email', 'email:dns'],
            'email' => ['email'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->guard('admin')->attempt($credentials)) {
            return $this->sendError('Login gagal', ['errors' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.'], 404);
        }

        $user = Auth::guard('admin')->user();
        $success['authorization'] = [
            'token' => $token,
            'type' => 'Bearer'
        ];
        $success['user'] = $user;

        return $this->sendResponse('Berhasil login', $success);
    }

    public function me(): JsonResponse
    {
        return $this->sendResponse('Berhasil mengambil data pengguna', ['user' => Auth::guard('admin')->user()]);
    }

    public function refresh(): JsonResponse
    {
        $user = Auth::guard('admin')->user();
        $success['authorization'] = [
            'token' => Auth::refresh(),
            'type' => 'bearer',
        ];
        $success['user'] = $user;

        return $this->sendResponse('Refresh token berhasil', $success);
    }

    public function logout(): JsonResponse
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if ($removeToken) {
            return $this->sendResponse('Berhasil logout');
        }
    }
}
