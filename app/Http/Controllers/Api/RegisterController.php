<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Mail\SendOtp;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['email', 'email:dns', 'unique:users'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $validateData = $validator->valid();
        $validateData['otp'] = rand(123456, 999999);
        $validateData['expired_at'] = Carbon::now()->addMinutes(10);
        $otp = Otp::find($validateData['email']);
        if ($otp) {
            $otp->update($validateData);
        } else {
            Otp::create($validateData);
        }

        $data = [
            'email' => $validateData['email'],
            'otp' => $validateData['otp']
        ];
        $this->sendOtp($data);

        return $this->sendResponse('Kode verifikasi berhasil dikirim', ['email' => $data['email']]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $validateData = $validator->valid();
        $validateData['otp'] = rand(123456, 999999);
        $validateData['expired_at'] = Carbon::now()->addMinutes(10);
        $otp = Otp::find($validateData['email']);

        if ($otp) {
            $otp->update($validateData);
            $data = [
                'email' => $validateData['email'],
                'otp' => $validateData['otp']
            ];
            $this->sendOtp($data);

            return $this->sendResponse('Kode verifikasi berhasil dikirim ulang', ['email' => $data['email']]);
        }

        return $this->sendError('Email tidak ditemukan', ["email" => $validateData["email"]]);
    }

    public function otpVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'otp' => ['required', 'integer']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $validateData = $validator->valid();
        $otp = Otp::find($validateData['email']);
        $now = Carbon::now();
        if ($otp) {
            if ($otp->otp != $validateData['otp']) {
                return $this->sendError('Kelasahan verifikasi otp', ['otp' => ['Kode verifikasi tidak sesuai.']]);
            } elseif ($otp->otp == $validateData['otp'] && $now->isAfter($otp->expired_at)) {
                return $this->sendError('Kelasahan verifikasi otp.', ['otp' => ['Kode verifikasi telah kadaluarsa.']]);
            }
            return $this->sendResponse('Kode verifikasi sesuai', ['email' => $validateData['email']]);
        }

        return $this->sendError('Email tidak ditemukan', ["email" => $validateData["email"]]);
    }

    public function createAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['email', 'email:dns'],
            'otp' => ['required'],
            'name' => ['required'],
            'password' => ['required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()]
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $validateData = $validator->valid();
        $otp = Otp::find($validateData['email']);
        if ($otp) {
            if ($otp->otp != $validateData['otp']) {
                return $this->sendError('Kelasahan verifikasi otp', ['otp' => ['Kode verifikasi tidak sesuai']]);
            }
        } else {
            return $this->sendError('Kode verifikasi tidak ditemukan', ["email" => $validateData["email"]]);
        }

        $user = User::create([
            'name' => $validateData['name'],
            'email' => $validateData['email'],
            'password' => Hash::make($validateData['password']),
        ]);

        Otp::find($validateData['email'])->delete();

        $token = Auth::guard('api')->login($user);
        $success['authorization'] = [
            'token' => $token,
            'type' => 'Bearer',
        ];
        $success['user'] = $user;

        if ($user) {
            return $this->sendResponse('Berhasil mendaftar', $success);
        }

        return $this->sendFail();
    }

    private function sendOtp($data)
    {
        $mailData = [
            'otp' => $data['otp'],
            'type' => 'activation'
        ];
        return Mail::to($data['email'])->queue(new SendOtp($mailData));
    }
}
