<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class OauthController extends BaseController
{
    public function redirectToProvider(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')->redirect()->getTargetUrl(),
        ]);
    }

    // public function redirectToProvider()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('gauth_id', $user->id)->first();

            if ($finduser) {
                $token = Auth::guard('api')->login($finduser);
                session()->regenerate();

                $success['authorization'] = [
                    'token' => $token,
                    'type' => 'Bearer',
                ];
                $success['user'] = $finduser;
                return $this->sendResponse('Berhasil login.', $success);
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'gauth_id' => $user->id,
                    'gauth_type' => 'google',
                    'password' => Hash::make(Str::random(8))
                ]);

                $token = Auth::gurad('api')->login($newUser);
                session()->regenerate();

                $success['authorization'] = [
                    'token' => $token,
                    'type' => 'Bearer',
                ];
                $success['user'] = $newUser;
                return $this->sendResponse('Berhasil login.', $success);
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
