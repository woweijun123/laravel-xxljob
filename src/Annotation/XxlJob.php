<?php

namespace XxlJob\Annotation;

use Attribute;
use XxlJob\Invoke\XxlJobCalleeEvent;

/**
 * 注解调用
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class XxlJob
{
    public function __construct(public XxlJobCalleeEvent|array $executor, public ?string $scope = null)
    {
    }
}
