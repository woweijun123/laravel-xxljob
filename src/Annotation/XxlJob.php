<?php

namespace XxlJob\Annotation;

use Attribute;
use XxlJob\Enum\Scope;

/**
 * 注解调用
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class XxlJob
{
    public function __construct(public string|array $executor, public ?string $scope = Scope::XxlJob->value)
    {
    }
}
