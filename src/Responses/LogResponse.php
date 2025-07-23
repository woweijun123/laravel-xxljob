<?php

namespace XxlJob\Responses;

use JsonSerializable;
use ReturnTypeWillChange;

/**
 * @property int $fromLineNum    本次请求，日志开始行数
 * @property int $toLineNum      本次请求，日志结束行号
 * @property string $logContent  本次请求日志内容
 * @property bool $isEnd         日志是否全部加载完
 */
class LogResponse implements JsonSerializable
{
    public int $fromLineNum = 0;
    public int $toLineNum = 100;
    public string $logContent = '';
    public bool $isEnd = true;

    #[ReturnTypeWillChange] public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fromLineNum' => $this->fromLineNum,
            'toLineNum' => $this->toLineNum,
            'logContent' => $this->logContent,
            'isEnd' => $this->isEnd,
        ];
    }
}
