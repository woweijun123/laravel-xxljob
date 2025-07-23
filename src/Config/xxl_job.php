<?php
return [
    // 是否启用
    'enable' => env('XXL_JOB_ENABLE', true),
    // XXL-JOB 服务端地址
    'admin_address' => env('XXL_JOB_ADMIN_ADDRESS', 'http://127.0.0.1:8080/xxl-job-admin'),
    // 对应的 AppName
    'app_name' => env('XXL_JOB_APP_NAME', 'xxl-job-demo'),
    // 访问凭证
    'access_token' => env('XXL_JOB_ACCESS_TOKEN', ''),
    // 执行器心跳间隔（秒）
    'heartbeat' => env('XXL_JOB_HEARTBEAT', 30),
    // 日志文件保存天数 [选填]: 过期日志自动清理, 限制值大于等于3时生效; 否则, 如-1, 关闭自动清理功能；
    'log_retention_days' => 30,
    // 执行器 HTTP Server 相关配置
    'executor_server' => [
        // HTTP Server 路由前缀
        'prefix_url' => env('XXL_JOB_PREFIX_URL', 'php-xxl-job'),
    ],
];
