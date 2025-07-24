<?php

namespace XxlJob\Invoke;

use XxlJob\Invoke\Exception\XxlJobInvokeException;
use BackedEnum;
use UnitEnum;

trait XxlJobCalleeEventTrait
{
    /**
     * 获取事件的命名空间
     * @return string
     */
    public function namespace(): string
    {
        return static::class;
    }

    /**
     * 获取事件的字符串名称
     * @return string
     * @throws
     */
    public function event(): string
    {
        return match (true) {
            $this instanceof BackedEnum => $this->value, // 有值枚举
            $this instanceof UnitEnum => $this->name, // 无值枚举
            default => throw new XxlJobInvokeException('This trait must in enum'),
        };
    }
}
