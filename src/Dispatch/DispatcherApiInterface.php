<?php

namespace XxlJob\Dispatch;

/**
 * 调度中心接口
 */
interface DispatcherApiInterface
{
    /**
     * 任务回调
     */
    public function callback(string $logId, string $logDateTim, int $handleCode = 200, string $handleMsg = null): mixed;

    /**
     * 执行器注册
     */
    public function registry(string $registryKey, string $registryValue): mixed;

    /**
     * 执行器注册摘除
     */
    public function registryRemove(string $registryKey, string $registryValue): mixed;
}
