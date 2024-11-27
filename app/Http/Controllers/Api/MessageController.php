<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class MessageController extends BaseController
{
    public function show(): JsonResponse
    {
        $messages = Chat::where('user_id', Auth::user()->id)->get();
        return $this->sendResponse('Berhasil ambil data pesan', ['messages' => $messages]);
    }

    public function store(Request $request, $id): JsonResponse
    {
        $admin = Admin::find($id);
        if (!$admin) return $this->sendError('Gagal mengirim pesan', ['error' => 'User tujuan tidak ditemukan']);

        $validator = Validator::make($request->all(), [
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Kesalahan validasi', $validator->errors(), 400);
        }

        $chat = Chat::where('user_id', Auth::user()->id)->where('admin_id', $id)->first();
        $chatid = '';
        if ($chat) {
            $chatid = $chat->id;
        } else {
            $chat = Chat::create([
                'user_id' => Auth::user()->id,
                'admin_id' => $id,
            ]);
            $chatid = $chat->id;
        }

        $message = Message::create([
            'chat_id' => $chatid,
            'message' => Crypt::encrypt($request->message),
            'status' => 0,
            'sent_by' => 'user'
        ]);

        if ($message) {
            $success['messages'] = $message;
            return $this->sendResponse('Berhasil mengirim pesan', $success);
        }
        return $this->sendFail();
    }

    public function read($id): JsonResponse
    {
        $chat = Chat::where('user_id', Auth::user()->id)->where('admin_id', $id)->first();
        if (!$chat) return $this->sendError('Gagal mengubah status baca', ['error' => 'Pesan tidak ditemukan']);

        $message = $chat->messages()->where('status', 0)->where('sent_by', 'admin');
        if ($message->get()->count() === 0) return $this->sendResponse('Gagal mengubah status baca', ['error' => 'Status pesan sudah terbaca semua']);

        $success['messages'] = $message->get()->makeHidden(['status']);
        if ($message->update(['status' => 1])) {
            return $this->sendResponse('Berhasil mengubah status baca', $success);
        }
        return $this->sendFail();
    }
}
