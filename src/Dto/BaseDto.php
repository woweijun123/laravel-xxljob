<?php

namespace XxlJob\Dto;

use ArrayAccess;

abstract class BaseDto implements ArrayAccess
{
    private array $container;

    public function __construct()
    {
        $this->container = get_object_vars($this);
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
