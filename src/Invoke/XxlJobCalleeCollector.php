<?php

namespace XxlJob\Invoke;

use ReflectionException;
use XxlJob\Annotation\XxlJobMapper;

class XxlJobCalleeCollector
{
    protected static array $container = [];

    /**
     * 添加被调用方法到容器
     * @param array             $callable [类名, 方法名]
     * @param string|array $event    事件枚举 或 [命名空间, 事件名] 格式的数组
     * @param string|null       $scope    作用域
     * @return void
     */
    public static function addCallee(array $callable, string|array $event, ?string $scope = null): void
    {
        try {
            $parameters = XxlJobReflection::reflectMethod(...$callable)->getParameters();
        } catch (ReflectionException) {
            $parameters = [];
        }

        $mapper = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $reflectionAttributes = $parameter->getAttributes(XxlJobMapper::class);
            foreach ($reflectionAttributes as $attribute) {
                $instance = $attribute->newInstance();
                foreach ($instance->src as $src) {
                    $mapper[$src] = $name;
                }
            }
            // 默认参数名优先级最低
            $mapper[$name] = $name;
        }

        static::$container[$scope][$event] = [$callable, $mapper];
    }

    /**
     * 检查是否存在对应的调用方法
     * @param string|array|null $event 事件枚举 或 [命名空间, 事件名] 格式的数组
     * @param string|null            $scope 作用域
     * @return bool
     */
    public static function hasCallee(string|array|null $event, ?string $scope = null): bool
    {
        if (!$event) {
            return false;
        }
        return isset(static::$container[$scope][$event]);
    }

    /**
     * 获取调用方法及参数映射
     * @param string|array|null $event 事件枚举 或 [命名空间, 事件名] 格式的数组
     * @param string|null            $scope 作用域
     * @return array|null
     */
    public static function getCallee(string|array|null $event, ?string $scope = null): ?array
    {
        if (!$event) {
            return null;
        }
        return static::$container[$scope][$event] ?? null;
    }

    /**
     * 清除容器数据
     * @param string|null $key 类名（可选）
     * @return void
     */
    public static function clear(?string $key = null): void
    {
        if ($key) {
            foreach (static::$container as $namespace => $events) {
                foreach ($events as $event => $callable) {
                    if ($callable[0][0] === $key) {
                        unset(static::$container[$namespace][$event]);
                    }
                }
            }
        } else {
            static::$container = [];
        }
    }
}
