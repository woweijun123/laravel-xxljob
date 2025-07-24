<?php declare(strict_types=1);

namespace XxlJob\Enum;

enum RedisKey: string
{
    use SprintfVal;
    // 回调方法注解的常量名
    case XxlJob = 'xxljob:%s';
}
