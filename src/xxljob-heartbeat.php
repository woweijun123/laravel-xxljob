<?php
/**
 * 极简 .env 文件解析器
 * @param $filePath
 * @return array
 */
function parseDotEnv($filePath): array
{
    $env = [];
    if (!file_exists($filePath)) {
        return $env;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 跳过注释行
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        // 解析 KEY=VALUE
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key   = trim($parts[0]);
            $value = trim($parts[1]);

            // 处理引号包裹的值
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            $env[$key] = $value;
        }
    }

    return $env;
}

/**
 * 获取配置值
 * @param $key
 * @param $default
 * @return array|mixed|string|null
 */
function getConfig($key, $default = null): mixed
{
    static $envVars = null;

    // 首次运行时加载 .env 文件
    if ($envVars === null) {
        $envVars = parseDotEnv(__DIR__ . '/.env');
    }

    // 1. 检查系统环境变量
    $envValue = getenv($key);
    if ($envValue !== false) {
        return $envValue;
    }

    // 2. 检查 .env 文件中的值
    if (isset($envVars[$key])) {
        return $envVars[$key];
    }

    // 3. 返回默认值
    return $default;
}

/**
 * 极简cURL心跳函数
 * @return array
 */
function sendHeartbeat(): array
{
    // 获取配置
    $adminUrl    = getConfig('XXL_JOB_URI', 'http://xxl-job-admin:8080/xxl-job-admin');
    $appName     = getConfig('XXL_JOB_APP_NAME', 'laravel-executor');
    $accessToken = getConfig('XXL_JOB_ACCESS_TOKEN', 'default_token');
    $executorUrl = getConfig('XXL_JOB_EXECUTOR_URI', 'http://localhost:8000');

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $adminUrl . '/api/registry',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'registryGroup' => 'EXECUTOR',
            'registryKey'   => $appName,
            'registryValue' => $executorUrl,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'XXL-JOB-ACCESS-TOKEN: ' . $accessToken,
        ],
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TCP_NODELAY    => true,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);

    curl_close($ch);

    return [$status, $response, $error];
}

/**
 * 信号处理器
 * @param $signo
 * @return void
 */
function signalHandler($signo): void
{
    global $lastHeartbeatTime, $heartbeatInterval;

    switch ($signo) {
        case SIGALRM:
            // 发送心跳
            sendHeartbeatWithRetry();
            // 记录最后心跳时间
            $lastHeartbeatTime = time();
            pcntl_alarm($heartbeatInterval);
            break;
        case SIGTERM:
        case SIGINT:
            // 优雅退出
            logMessage("收到终止信号，正在退出...");
            exit(0);
            break;
        default:
            break;
    }
}

/**
 * 带重试的心跳发送
 */
function sendHeartbeatWithRetry(): bool
{
    global $maxRetries, $retryDelay;

    $retryCount = 0;
    $success    = false;

    while ($retryCount < $maxRetries && !$success) {
        [$status, $response, $error] = sendHeartbeat();

        if ($status === 200) {
            logMessage("心跳成功");
            $success = true;
        } else {
            $retryCount++;
            $errorMsg = $error ?: "HTTP $status: " . substr($response, 0, 100);
            logMessage("心跳失败 [$retryCount/$maxRetries]: $errorMsg");

            if ($retryCount < $maxRetries) {
                sleep($retryDelay);
            }
        }
    }

    return $success;
}

/**
 * 精简日志函数
 */
function logMessage($message): void
{
    $timestamp = date('Y-m-d H:i:s');
    $memUsage  = round(memory_get_usage() / 1024); // KB
    echo "[$timestamp] [{$memUsage}KB] $message\n";
}

// 内存优化设置
gc_enable();
ini_set('memory_limit', '16M');
// 启用异步信号处理
pcntl_async_signals(true);
// 获取进程ID
$pid = getmypid();

// 配置参数
$heartbeatInterval = (int)getConfig('XXL_JOB_HEARTBEAT', 30);
$maxRetries        = (int)getConfig('XXL_JOB_MAX_RETRIES', 3);
$retryDelay        = (int)getConfig('XXL_JOB_RETRY_DELAY', 10);

// 最后心跳时间
$lastHeartbeatTime = time();

// 注册信号处理器
pcntl_signal(SIGALRM, "signalHandler");  // 定时器信号
pcntl_signal(SIGTERM, "signalHandler");  // 终止信号
pcntl_signal(SIGINT, "signalHandler");   // Ctrl+C

// 设置初始定时器
pcntl_alarm($heartbeatInterval);

logMessage("XXL-JOB 心跳守护进程启动 (信号模式)");
logMessage("进程ID: $pid; 配置: 心跳间隔={$heartbeatInterval}秒, 重试={$maxRetries}次, 重试间隔={$retryDelay}秒");
// 主循环
while (true) {
    sleep(10); // 睡眠10秒，空闲时减少CPU占用
    // 每60秒执行一次垃圾回收
    if (time() % 60 === 0) {
        gc_collect_cycles();
    }
}