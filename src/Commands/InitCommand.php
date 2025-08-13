<?php

namespace XxlJob\Commands;

use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;
use XxlJob\Providers\XxlJobProvider;

class InitCommand extends Command
{
    protected $signature   = 'xxljob:init';
    protected $description = 'XXL-JOB 清除缓存和执行器初始化';

    #[NoReturn] public function handle(): void
    {
        if (is_file($xxljobPath = XxlJobProvider::getCachedPath('cache/xxljob.php'))) {
            @unlink($xxljobPath);
        }
        $this->info('XXL-JOB 清除缓存 成功');
    }
}
