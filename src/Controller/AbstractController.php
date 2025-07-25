<?php

namespace XxlJob\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class AbstractController extends Controller
{
    /**
     * @desc 成功返回
     * @param array $data
     * @param string|null $msg
     * @param int $code
     * @return JsonResponse
     */
    public function success(array $data = [], string|null $msg = null, int $code = 200): JsonResponse
    {
        return $this->jsonResponse($data, $msg, $code);
    }

    /**
     * @desc Json响应
     * @param array $data
     * @param string|null $msg
     * @param int $code
     * @return JsonResponse
     */
    public function jsonResponse(array $data = [], string|null $msg = null, int $code = 0): JsonResponse
    {
        $return = ['code' => $code, 'msg' => $msg];
        if ($data) {
            $return['content'] = $data;
        }
        return new JsonResponse($return, $code);
    }
}
