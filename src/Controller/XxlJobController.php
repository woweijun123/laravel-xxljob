<?php

namespace XxlJob\Controller;

use Illuminate\Http\JsonResponse;
use XxlJob\Executor\ExecutorApiInterface;
use XxlJob\Requests\JobIdRequest;
use XxlJob\Requests\LogRequest;
use XxlJob\Requests\RunRequest;

class XxlJobController extends AbstractController
{
    public function __construct(private readonly ExecutorApiInterface $executor)
    {
    }

    /**
     * 心跳检测
     * @return JsonResponse
     */
    public function beat(): JsonResponse
    {
        $this->executor->beat();
        return $this->success();
    }

    /**
     * 忙碌检测
     * @param JobIdRequest $request
     * @return JsonResponse
     */
    public function idleBeat(JobIdRequest $request): JsonResponse
    {
        return $this->success($this->executor->idleBeat($request->jobId));
    }

    /**
     * 触发任务
     * @param RunRequest $request
     * @return JsonResponse
     */
    public function run(RunRequest $request): JsonResponse
    {
        $this->executor->run($request);
        return $this->success();
    }

    /**
     * 终止任务
     * @param JobIdRequest $request
     * @return JsonResponse
     */
    public function kill(JobIdRequest $request): JsonResponse
    {
        return $this->success($this->executor->kill($request->jobId));
    }

    /**
     * 查看执行日志
     * @param LogRequest $request
     * @return JsonResponse
     */
    public function log(LogRequest $request): JsonResponse
    {
        return $this->success($this->executor->log($request));
    }
}
