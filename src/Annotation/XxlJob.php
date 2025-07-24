<?php

namespace XxlJob\Annotation;

use XxlJob\Invoke\XxlJobCalleeEvent;
use Attribute;

/**
 * 注解调用
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class XxlJob
{
    public function __construct(protected string $executor = '')
    {
    }
}
