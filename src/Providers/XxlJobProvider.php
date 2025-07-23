<?php

namespace XxlJob\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Throwable;
use XxlJob\Dispatch\DispatcherInterfaceImpl;
use XxlJob\Dispatch\DispatcherInterface;
use XxlJob\Executor\ExecutorInterfaceImpl;
use XxlJob\Executor\ExecutorInterface;

class XxlJobProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/xxl_job.php', 'xxl_job');
        // 绑定接口到实现类
        $this->app->bindIf(ExecutorInterface::class, ExecutorInterfaceImpl::class);
        $this->app->bindIf(DispatcherInterface::class, DispatcherInterfaceImpl::class);
    }

    /**
     * 启动服务提供者
     * 在这个方法中注册各种通过注解发现的服务、回调和AMQP消费者。
     * @throws Throwable
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../Config/xxl_job.php' => config_path('xxl_job.php')]);
        // 自动加载路由文件
        if (file_exists($routesFile = __DIR__ . '/../Route/xxl-job.php')) {
            Route::group(['namespace' => 'XxlJob\Controller'], function () use ($routesFile) {
                require $routesFile;
            });
        }
    }
}
