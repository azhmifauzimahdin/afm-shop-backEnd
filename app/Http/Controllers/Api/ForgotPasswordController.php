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
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends BaseController
{
    public function forgetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['email', 'email:dns'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors());
        }

        $validateData = $validator->valid();
        $user = User::where('email', $validateData['email'])->first();
        if (!$user) {
            return $this->sendError('Kesalahan validasi', ["email" => ["Kami tidak dapat menemukan pengguna dengan alamat email tersebut."]]);
        }

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
        // $this->sendOtp($data);

        return $this->sendResponse('Kode verifikasi berhasil dikirim', ["email" => $data["email"]]);
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
            // $this->sendOtp($data);

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

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['email', 'email:dns'],
            'otp' => ['required'],
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

        $user = User::where('email', $validateData['email'])->first();
        if (!$user) {
            return $this->sendError('Kesalahan validasi', ["email" => "Kami tidak dapat menemukan pengguna dengan alamat email tersebut."]);
        }

        $user->update([
            'password' => $validateData["password"]
        ]);

        Otp::find($validateData['email'])->delete();

        $token = Auth::guard('api')->login($user);
        $success['authorization'] = [
            'token' => $token,
            'type' => 'Bearer',
        ];
        $success['user'] = $user;

        if ($user) {
            return $this->sendResponse('Berhasil ganti kata sandi', $success);
        }

        return $this->sendFail();
    }

    private function sendOtp($data)
    {
        $mailData = [
            'otp' => $data['otp'],
            'type' => 'resetPassword'
        ];
        return Mail::to($data['email'])->queue(new SendOtp($mailData));
    }
}
