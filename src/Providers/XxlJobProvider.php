<?php

namespace XxlJob\Providers;

use Illuminate\Support\ServiceProvider;
use Throwable;

class XxlJobProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/xxl_job.php', 'xxl_job');
    }

    /**
     * 启动服务提供者
     * 在这个方法中注册各种通过注解发现的服务、回调和AMQP消费者。
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../Config/xxl_job.php' => config_path('xxl_job.php')]);
    }
}
