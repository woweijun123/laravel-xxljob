<?php

namespace XxlJob\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\NoReturn;
use XxlJob\Enum\RedisKey;

class InitCommand extends Command
{
    protected $signature   = 'xxljob:init';
    protected $description = 'XXL-JOB 清除缓存和执行器初始化';

    #[NoReturn] public function handle(): void
    {
        $appName = env('APP_NAME');
        dump(Cache::forget(RedisKey::XxlJob->spr($appName)));
        $this->info('XXL-JOB 清除缓存 成功');
    }
}
