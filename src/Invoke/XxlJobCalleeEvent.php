<?php

namespace XxlJob\Invoke;

use BackedEnum;

/**
 * @mixin BackedEnum
 */
interface XxlJobCalleeEvent
{
    /**
     * 获取事件的命名空间
     * @return string
     */
    public function namespace(): string;

    /**
     * 获取事件的字符串名称
     * @return string
     */
    public function event(): string;
}
