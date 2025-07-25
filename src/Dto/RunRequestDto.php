<?php

namespace XxlJob\Dto;

class RunRequestDto extends BaseDto
{
    /**
     * @param int $jobId 任务ID
     * @param string $executorHandler 执行器Handler
     * @param ?string $executorParams 执行器参数, Nullable string.
     * @param string $executorBlockStrategy 执行器阻塞策略
     * @param int $executorTimeout 执行器超时时间 (秒)
     * @param int $logId 日志ID
     * @param int $logDateTime 日志时间 (Unix timestamp)
     * @param string $glueType Glue类型
     * @param ?string $glueSource Glue源码, Nullable string.
     * @param int $glueUpdatetime Glue更新时间 (Unix timestamp)
     * @param int $broadcastIndex 广播索引
     * @param int $broadcastTotal 广播总数
     */
    public function __construct(
        public int     $jobId = 0,
        public string  $executorHandler = '',
        public ?string $executorParams = null,
        public string  $executorBlockStrategy = '',
        public int     $executorTimeout = 0,
        public int     $logId = 0,
        public int     $logDateTime = 0,
        public string  $glueType = '',
        public ?string $glueSource = null,
        public int     $glueUpdatetime = 0,
        public int     $broadcastIndex = 0,
        public int     $broadcastTotal = 0
    )
    {
        parent::__construct();
    }
}
