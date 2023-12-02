<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function responseData($data)
    {
        $code = JsonResponse::HTTP_OK;
        if (isset($data['code']) && is_numeric($data['code'])) {
            $code = $data['code'];
            unset($data['code']);
        }
        return response()->json($data, $code);
    }

    public function successResponse($data = ['message' => 'OK'])
    {
        return response()->json($data);
    }

    public function errorResponse($errorData = null)
    {
        $errorData = $errorData ? $errorData : ['errors' => 'Đã có lỗi xảy ra'];
        return response()->json($errorData, array_key_exists('code',$errorData)
        ? $errorData['code']
        : JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function resultResponse($data)
    {
        return !array_key_exists('errors',$data) ? $this->successResponse($data) : $this->errorResponse($data);
    }
}
