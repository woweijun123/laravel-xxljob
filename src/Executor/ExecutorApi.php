<?php

namespace XxlJob\Executor;

use XxlJob\Jobs\ExecutorJob;
use XxlJob\Requests\LogRequest;
use XxlJob\Requests\RunRequest;
use XxlJob\Responses\LogResponse;

class ExecutorApi implements ExecutorApiInterface
{
    /**
     * 心跳检测
     */
    public function beat(): array
    {
        return [];
    }

    /**
     * 忙碌检测
     * @param int $jobId
     * @return array
     */
    public function idleBeat(int $jobId): array
    {
        return [];
    }

    /**
     * 触发任务执行
     * @param RunRequest $request
     * @return void
     */
    public function run(RunRequest $request): void
    {
        ExecutorJob::dispatch($request);
    }

    /**
     * 终止任务
     * @param int $jobId
     * @return array
     */
    public function kill(int $jobId): array
    {
        return [];
    }

    /**
     * 终止任务，滚动方式加载
     * @param LogRequest $request
     * @return array
     */
    public function log(LogRequest $request): array
    {
        return app(LogResponse::class)->toArray();
    }
}
