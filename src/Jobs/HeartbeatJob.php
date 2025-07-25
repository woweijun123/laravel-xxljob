<?php

namespace XxlJob\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Throwable;
use XxlJob\Dispatch\DispatcherApi;
use XxlJob\Invoke\XxlJobCalleeCollector;

readonly class HeartbeatJob
{
    public function __construct(private DispatcherApi $dispatcherApi)
    {
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            // 调用自定义方法发送心跳
            $result = $this->registry()();
            if (!$result) {
                retry(3, $this->registry(), 10); // 重试3次，间隔10秒
            }
            Log::info('XXL-JOB 执行器心跳维持 成功');
        })->everyThirtySeconds(); // 每30秒执行
    }

    /**
     * 注册执行器
     * @return callable
     */
    protected function registry(): callable
    {
        return function () {
            try {
                $executors = array_keys(XxlJobCalleeCollector::getContainer());
                foreach ($executors as $executor) {
                    $this->dispatcherApi->registry($executor, config('xxljob.executor_uri'));
                }
                return true;
            } catch (Throwable $e) {
                Log::error('XXL-JOB 执行器心跳维持 失败：' . $e->getMessage());
                return false;
            }
        };
    }
}
