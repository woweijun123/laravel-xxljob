<?php

namespace XxlJob\Dto;

use ArrayAccess;
use Illuminate\Database\Eloquent\Model;

abstract class BaseDto implements ArrayAccess
{
    private array $container;

    public function __construct()
    {
        $this->container = get_object_vars($this);
    }

    /**
     * 静态获取Struct
     */
    public static function make(Model|null|array $form): static
    {
        if (empty($form)) {
            return new static();
        }
        if ($form instanceof Model) {
            $form = $form->toArray();
        }
        // 过滤不存在的属性
        $form = array_intersect_key($form, get_class_vars(static::class));
        return new static(...$form);
    }


    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }
}
