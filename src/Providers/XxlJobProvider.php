<?php

namespace XxlJob\Providers;

use FilesystemIterator;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use XxlJob\Annotation\XxlJob;
use XxlJob\Dispatch\DispatcherInterfaceImpl;
use XxlJob\Dispatch\DispatcherInterface;
use XxlJob\Enum\RedisKey;
use XxlJob\Executor\ExecutorInterfaceImpl;
use XxlJob\Executor\ExecutorInterface;
use XxlJob\Invoke\Reflection;
use XxlJob\Invoke\XxlJobCalleeCollector;

class XxlJobProvider extends ServiceProvider
{
    private int $cacheTime = 86400; // 缓存时间「秒」

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
                require_once $routesFile;
            });
        }
        // 注册通过 @XxlJob 注解发现的回调方法
        $this->registerXxlJobMethod();
    }

    /**
     * 注册通过 `@XxlJob` 注解标记的回调方法。
     * 它会扫描指定目录下的PHP文件，查找带有 `App\Annotation\XxlJob` 注解的方法，
     * 并将这些方法添加到 `XxlJobCollector` 中，以便后续可以根据事件和作用域进行调用。
     */
    protected function registerXxlJobMethod(): void
    {
        // 从缓存或通过扫描发现 XxlJob 方法
        $methods = $this->getBindings(self::getType(RedisKey::XxlJob), [$this, 'discoverXxlJobMethods']);
        foreach ($methods as $callable) {
            [$callable, $event, $scope] = $callable;
            // 将发现的回调方法添加到回调收集器中
            XxlJobCalleeCollector::addCallee($callable, is_string($event) ? unserialize($event) : $event, $scope);
        }
    }

    /**
     * 发现带有 `@XxlJob` 注解的方法，并返回包含回调信息的数组。
     * 扫描 'app/Services' 目录下的PHP文件。
     * @return array
     */
    protected function discoverXxlJobMethods(): array
    {
        // 扫描指定目录下的PHP文件
        $files         = $this->scanPhpFiles([app_path('Services')]);
        $bindings = [];

        foreach ($files as $file) {
            try {
                // 根据文件路径获取完整的类名
                $className = $this->getClassFromFilePath($file);
                // 如果类不存在则跳过
                if (!class_exists($className)) {
                    continue;
                }
                $reflection = Reflection::reflectClass($className);
                // 遍历类的所有方法
                foreach ($reflection->getMethods() as $method) {
                    // 遍历方法上所有 XxlJob 注解
                    foreach ($method->getAttributes(XxlJob::class) as $attribute) {
                        // 实例化注解对象
                        $callee = $attribute->newInstance();
                        // 收集回调信息：[类名, 方法名], 事件名, 作用域
                        $bindings[] = [[$className, $method->getName()], serialize($callee->event), $callee->scope];
                    }
                }
            } catch (Throwable $e) {
                // 记录警告日志，跳过处理失败的文件
                Log::warning("跳过文件: $file", ['exception' => $e]);
            }
        }

        return $bindings;
    }

    /**
     * 获取绑定配置，优先从缓存读取，如果缓存不存在或环境非 'local' 则通过回调函数发现，
     * 并将结果存入缓存。
     * @param string $type 绑定类型
     * @param callable $discoverCallback 发现绑定的回调函数
     * @return array
     */
    private function getBindings(string $type, callable $discoverCallback): array
    {
        // 如果不是本地环境，尝试从缓存读取
        if (!app()->environment('local')) {
            try {
                /** @var Repository $cache */
                $cached = $this->getCache()->get($type);
                // 如果缓存中存在且是数组，则直接返回
                if (is_array($cached) && !empty($cached)) {
                    return $cached;
                }
            } catch (Throwable $e) {
                // 缓存读取失败，记录警告并降级为本地扫描
                Log::warning("缓存读取失败，降级为本地扫描: key=$type", ['exception' => $e]);
            }
        }
        // 如果缓存不存在或读取失败，则通过回调函数发现绑定
        $bindings = $discoverCallback();

        // 将发现的绑定结果永久存入缓存
        $this->getCache()->put($type, $bindings, $this->cacheTime);
        return $bindings;
    }

    /**
     * 获取缓存仓库实例，优先使用 Redis，如果 Redis 不可用则降级到文件缓存。
     * @return Repository
     */
    private function getCache(): Repository
    {
        try {
            // 尝试使用 Redis 缓存
            return Cache::store('redis');
        } catch (Throwable $e) {
            // Redis 缓存不可用，记录警告并尝试使用文件缓存
            Log::warning('Redis 缓存不可用，已降级为文件缓存', ['exception' => $e]);
            try {
                // 尝试使用文件缓存
                return Cache::store('file');
            } catch (Throwable $eFile) {
                // 文件缓存也不可用，记录错误并抛出异常
                Log::error('文件缓存也不可用，请检查配置', ['exception' => $eFile]);
            }
        }
    }


    /**
     * 扫描指定目录数组下的所有PHP文件。
     * 使用静态变量缓存已扫描的文件列表以提高性能。
     * @param array $dirs 要扫描的目录路径数组
     * @return array 找到的PHP文件路径数组
     */
    protected function scanPhpFiles(array $dirs): array
    {
        // 静态缓存，避免重复扫描
        static $scannedFiles = [];
        // 为目录数组生成一个唯一的缓存键
        $key = md5(serialize($dirs));
        // 如果已扫描过，直接返回缓存结果
        if (isset($scannedFiles[$key])) {
            return $scannedFiles[$key];
        }

        $files = [];
        // 遍历所有目录并合并扫描结果
        foreach ($dirs as $dir) {
            $files = array_merge($files, $this->getPhpFilesInDir($dir));
        }

        // 缓存扫描结果并返回
        return $scannedFiles[$key] = $files;
    }

    /**
     * 获取指定目录下所有PHP文件的路径。
     * 使用递归迭代器遍历目录及其子目录。
     * @param string $dir 要扫描的目录路径
     * @return array PHP文件路径数组
     */
    protected function getPhpFilesInDir(string $dir): array
    {
        // 如果目录不存在，返回空数组
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        // 创建递归迭代器，跳过 '.' 和 '..'
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        // 遍历所有文件
        foreach ($iterator as $file) {
            // 如果是文件且扩展名为 'php'，则添加到结果数组
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * 根据PHP文件的绝对路径解析出其完整的类名。
     * 假设文件在 `app_path()` 目录下，并遵循PSR-4命名规范。
     * @param string $file PHP文件的绝对路径
     * @return string 完整的类名
     * @throws InvalidArgumentException 如果文件不在 app 目录下
     */
    protected function getClassFromFilePath(string $file): string
    {
        $appPath = app_path();
        // 检查文件是否在 app 目录下
        if (!str_starts_with($file, $appPath)) {
            throw new InvalidArgumentException("文件 [$file] 不在 app 目录下，无法解析类名。");
        }

        // 获取相对于 app 目录的相对路径
        $relativePath = substr($file, strlen($appPath) + 1);
        // 将路径分隔符替换为命名空间分隔符，并移除 .php 扩展名
        $className = strtr($relativePath, ['/' => '\\', '.php' => '']);

        // 拼接成完整的命名空间类名（假设 app 目录对应 App 命名空间）
        return "App\\$className";
    }

    /**
     * 获取缓存键
     * @param RedisKey $key
     * @return string
     */
    protected static function getType(RedisKey $key): string
    {
        return $key->spr(env('APP_NAME', 'laravel-xxljob'));
    }
}
