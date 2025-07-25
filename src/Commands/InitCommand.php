<?php

namespace XxlJob\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\NoReturn;
use Throwable;
use XxlJob\Dispatch\DispatcherApi;
use XxlJob\Enum\RedisKey;
use XxlJob\Invoke\XxlJobCalleeCollector;

class InitCommand extends Command
{
    protected $signature   = 'xxljob:init';
    protected $description = 'XXL-JOB 清除缓存和执行器初始化';

    public function __construct(private readonly DispatcherApi $dispatcherApi)
    {
        parent::__construct();
    }

    #[NoReturn] public function handle(): void
    {
        $appName = env('APP_NAME');
        dump(Cache::forget(RedisKey::XxlJob->spr($appName)));
        $this->info('XXL-JOB 清除缓存 成功');
        try {
            $container = XxlJobCalleeCollector::getContainer();
            foreach ($container as $executor) {
                $this->dispatcherApi->registry($executor, config('xxljob.executor_uri'));
            }
            $this->info('XXL-JOB 执行器注册 成功');
        } catch (Throwable $e) {
            $this->error('XXL-JOB 执行器注册 失败：' . $e->getMessage());
        }
    }
}
