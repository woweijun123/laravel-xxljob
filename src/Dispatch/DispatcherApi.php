<?php

namespace XxlJob\Dispatch;

use XxlJob\Api\BaseClient;

/**
 * 调度中心接口实现
 */
class DispatcherApi extends BaseClient implements DispatcherApiInterface
{
    protected array $configExt = [
        'decode' => false,          // 是否解析返回数据
    ];

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

    public function callback(): mixed
    {
    }

    public function registryRemove(string $registryKey, string $registryValue): mixed
    {
    }
}
