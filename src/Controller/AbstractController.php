<?php

namespace XxlJob\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class AbstractController extends Controller
{
    /**
     * @desc 成功返回
     * @param array $data
     * @param string $msg
     * @param int $code
     * @return JsonResponse
     */
    public function success(array $data = [], string $msg = 'success', int $code = 200): JsonResponse
    {
        return $this->jsonResponse($data, $msg, $code);
    }

    /**
     * @desc Json响应
     * @param array $data
     * @param string $msg
     * @param int $code
     * @return JsonResponse
     */
    public function jsonResponse(array $data = [], string $msg = '', int $code = 0): JsonResponse
    {
        return new JsonResponse([
            'code' => $code,
            'msg' => $msg,
            'content' => $data,
        ], $code);
    }
}
