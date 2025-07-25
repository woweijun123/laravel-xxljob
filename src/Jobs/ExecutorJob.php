<?php

namespace XxlJob\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use XxlJob\Invoke\XxlJobReflection;

class ExecutorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle($request): void
    {
        XxlJobReflection::call(event: $request->executorHandler, args: compact('request'));
    }
}
