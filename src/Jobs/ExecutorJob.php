<?php

namespace XxlJob\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use XxlJob\Dispatch\DispatcherApi;
use XxlJob\Dto\RunRequestDto;
use XxlJob\Invoke\XxlJobReflection;

class ExecutorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 公共属性会自动序列化
    public function __construct(public RunRequestDto $request)
    {
        // 设置队列属性（可选）
        $this->onQueue('executor'); // 指定队列名称
        $this->onConnection('redis'); // 指定连接
    }

    public function handle(): void
    {
        $handleMsg = 'success';
        $call = XxlJobReflection::call(event: $this->request->executorHandler, args: ['request' => $this->request]);
        if (!empty($call) && is_array($call)) {
            $handleMsg = json_encode($call, 256);
        }
        app(DispatcherApi::class)->callback($this->request->logId, $this->request->logDateTime, $handleMsg);
    }
}
