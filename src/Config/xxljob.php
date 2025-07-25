<?php
return [
    // 是否启用
    'enable' => env('XXL_JOB_ENABLE', true),
    // 心跳间隔，单位秒
    'heartbeat' => env('XXL_JOB_HEARTBEAT', 30),
    // XXL-JOB 服务端地址
    'uri' => env('XXL_JOB_URI', 'http://127.0.0.1:8080/xxl-job-admin'),
    // 执行器地址
    'executor_uri' => env('XXL_JOB_EXECUTOR_URI', 'http://127.0.0.1/xxljob'),
    // 对应的 AppName
    'app_name' => env('XXL_JOB_APP_NAME', 'xxl-job-demo'),
    // 访问凭证
    'access_token' => env('XXL_JOB_ACCESS_TOKEN', 'xxljob'),
];
