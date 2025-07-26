<?php

namespace XxlJob\Dispatch;

use Illuminate\Support\Facades\Log;
use XxlJob\Api\BaseClient;

/**
 * 调度中心接口实现
 */
class DispatcherApi extends BaseClient implements DispatcherApiInterface
{
    protected array $configExt = [
        'decode' => false,          // 是否解析返回数据
    ];

    public function __construct()
    {
        $this->apiUri = config('xxljob.uri');
    }


    /**
     * 执行器注册
     * @param string $registryKey
     * @param string $registryValue
     * @return mixed
     */
    public function registry(string $registryKey, string $registryValue): mixed
    {
        return $this->send(
            '/api/registry',
            [
                'registryGroup' => 'EXECUTOR',
                'registryKey' => $registryKey,
                'registryValue' => $registryValue,
            ]
        );
    }

    /**
     * 任务回调
     * @param string $logId
     * @param string $logDateTim
     * @param int $handleCode
     * @param string|null $handleMsg
     * @return mixed
     */
    public function callback(string $logId, string $logDateTim, int $handleCode = 200, string $handleMsg = null): mixed
    {
        $postData = [
            [
                'logId' => $logId,
                'logDateTim' => $logDateTim,
                'handleCode' => $handleCode,
                'handleMsg' => $handleMsg,
            ],
        ];
        Log::info('xxl-job-request', [$postData]);
        $callback = $this->send('/api/callback', $postData);
        Log::info('xxl-job-callback', [$callback]);
        return $callback;
    }

    /**
     * 执行器注册摘除
     * @param string $registryKey
     * @param string $registryValue
     * @return mixed
     */
    public function registryRemove(string $registryKey, string $registryValue): mixed
    {
        // 暂未实现
    }
}
