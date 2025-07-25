<?php

namespace XxlJob\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;
use XxlJob\Dispatch\DispatcherApi;

class HeartbeatCommand extends Command
{
    protected $signature = 'xxljob:heartbeat';
    protected $description = '发送心跳到XXL-JOB';
    protected int $maxRetries; // 最大重试次数
    protected int $retryDelay; // 重试间隔「秒」
    private int $heartbeat; // 心跳时间「秒」

    public function __construct(protected DispatcherApi $dispatcherApi)
    {
        parent::__construct();
        $this->heartbeat = config('xxljob.heartbeat');
        $this->maxRetries = config('xxljob.max_retries');
        $this->retryDelay = config('xxljob.retry_delay');
    }

    public function handle(): void
    {
        $this->keepalive();
        while (true) {
            sleep(10);
        }
    }

    /**
     * 注册执行器
     * @return callable
     */
    protected function registry(): callable
    {
        return function () {
            try {
                $this->dispatcherApi->registry(config('xxljob.app_name'), config('xxljob.executor_uri'));
                return true;
            } catch (Throwable $e) {
                Log::error('XXL-JOB 执行器心跳维持 失败：' . $e->getMessage());
                return false;
            }
        };
    }

    /**
     * 保持心跳
     * @return void
     */
    public function keepalive(): void
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            try {
                // 手动触发心跳响应
                $result = $this->registry()();
                if (!$result) {
                    retry($this->maxRetries, $this->registry(), $this->retryDelay); // 重试3次，间隔10秒
                }
            } catch (Throwable $e) {
                Log::error('XXL-JOB 执行器心跳维持 失败：' . $e->getMessage());
            }
            pcntl_alarm($this->heartbeat);
        });
        pcntl_alarm($this->heartbeat);
    }
}
