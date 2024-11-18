<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function sendResponse($message, $result = [])
    {
        $response = [
            'code'    => 200,
            'success' => true,
            'message' => $message,
            'data'    => $result,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'code' => $code,
            'success' => false,
            'message' => $error
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function sendFail()
    {
        $response = [
            'code' => 400,
            'success' => false,
            'message' => "Bad Request"
        ];

        return response()->json($response, 400);
    }
}
