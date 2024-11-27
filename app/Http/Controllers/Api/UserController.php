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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends BaseController
{
    public function updateUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'gender' => [Rule::in(['male', 'female'])],
            'birthday' => ['date', 'before_or_equal:now', 'nullable'],
            'image' => ['image', 'mimes:jpg,jpeg,png', 'max:1000']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $validateData = $validator->valid();
        $user = User::find(Auth::user()->id);
        if ($user) {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('images/users/', 'public');
                if ($user->image) {
                    $file_path = public_path('storage/images/users/' . $user->image);
                    if (File::exists($file_path)) {
                        File::delete($file_path);
                    }
                }
                $validateData['image'] = basename($path);
                $user->update($validateData);
            } else {
                $user->update($validateData);
            }
        } else {
            return $this->sendError('User tidak ditemukan');
        }

        $success['user'] = $user->makeVisible(['birthday', 'gender']);;
        return $this->sendResponse('Berhasil ubah profil', $success);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:dns']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
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
            'email' => ['required', 'email:dns']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
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

    public function verifikasiOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'otp' => ['required', 'integer']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
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
        } else {
            return $this->sendError('Email tidak ditemukan', ["email" => $validateData["email"]]);
        }

        $user = User::find(Auth::user()->id);
        $user->update([
            'email' => $validateData['email']
        ]);
        Otp::find($validateData['email'])->delete();
        $success['user'] = $user;
        return $this->sendResponse('Berhasil ubah email', $success);
    }

    private function sendOtp($data)
    {
        $mailData = [
            'otp' => $data['otp'],
            'type' => 'verification'
        ];
        return Mail::to($data['email'])->queue(new SendOtp($mailData));
    }

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
        $user = User::find(Auth::user()->id);
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

        $success['user'] = $user->makeVisible(['birthday', 'gender']);;
        return $this->sendResponse('Berhasil ubah kata sandi', $success);
    }
}
