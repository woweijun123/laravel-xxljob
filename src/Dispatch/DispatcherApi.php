<?php

namespace XxlJob\Dispatch;

use XxlJob\Api\BaseClient;

/**
 * 调度中心接口实现
 */
class DispatcherApi extends BaseClient implements DispatcherApiInterface
{
    /**
     * 执行器注册
     * @param string $registryKey
     * @param string $registryValue
     * @return bool
     */
    public function registry(string $registryKey, string $registryValue): bool
    {
        return $this->send(
            [
                'uri' => '${API_URI}/api/registry',
            ],
            [
                'registryGroup' => 'EXECUTOR',
                'registryKey' => $registryKey,
                'registryValue' => $registryValue,
            ]
        );
    }

    public function callback()
    {
    }

    public function registryRemove(string $registryKey, string $registryValue)
    {
    }
}
