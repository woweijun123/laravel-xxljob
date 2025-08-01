<?php

namespace XxlJob\Executor;

use XxlJob\Controller\Response;
use XxlJob\Requests\LogRequest;
use XxlJob\Requests\RunRequest;

/**
 * 执行器接口
 */
interface ExecutorApiInterface
{
    /**
     * 心跳检测
     */
    public function beat();

    /**
     * 忙碌检测
     * @param int $jobId
     * @return array
     */
    public function idleBeat(int $jobId): array;

    /**
     * 触发任务
     * @param RunRequest $request
     * @return void
     */
    public function run(RunRequest $request): void;

    /**
     * 终止任务
     * @param int $jobId
     * @return array
     */
    public function kill(int $jobId): array;

    /**
     * 查看执行日志
     * @param LogRequest $request
     * @return array
     */
    public function log(LogRequest $request): array;
}
