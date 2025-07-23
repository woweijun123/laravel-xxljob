<?php

namespace XxlJob\Dispatch;

/**
 * 调度中心接口
 */
interface DispatcherInterface
{
    /**
     * 任务回调
     */
    public function callback();

    /**
     * 执行器注册
     */
    public function registry(string $registryKey, string $registryValue);

    /**
     * 执行器注册摘除
     */
    public function registryRemove(string $registryKey, string $registryValue);
}
