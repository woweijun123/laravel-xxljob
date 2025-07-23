<?php

namespace XxlJob\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use XxlJob\Executor\ExecutorInterface;
use XxlJob\Requests\JobIdRequest;
use XxlJob\Requests\LogRequest;
use XxlJob\Requests\RunRequest;

class XxlJobController extends AbstractController
{
    public function __construct(private readonly ExecutorInterface $executor)
    {
    }

    public function beat(Request $request): JsonResponse
    {
        $this->executor->beat();
        return $this->success();
    }

    public function idleBeat(JobIdRequest $request): JsonResponse
    {
        return $this->success($this->executor->idleBeat($request->jobId));
    }

    public function run(RunRequest $request): JsonResponse
    {
        return $this->success($this->executor->run($request));
    }

    public function kill(Request $request): JsonResponse
    {
        return $this->success($this->executor->kill($this->safeGetJobId($request)));
    }

    public function log(LogRequest $request): JsonResponse
    {
        return $this->success($this->executor->log($request));
    }

    /**
     * 获取jobId
     * @param Request $request
     * @return int
     */
    private function safeGetJobId(Request $request): int
    {
        return intval($request->input('jobId', 0));
    }
}
