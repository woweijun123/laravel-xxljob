-----

# riven/laravel-amqp

[](https://www.google.com/search?q=//packagist.org/packages/riven/laravel-amqp)
[](https://www.google.com/search?q=//packagist.org/packages/riven/laravel-amqp)
[](https://www.google.com/search?q=//packagist.org/packages/riven/laravel-amqp)

`php-amqplib` 的 Laravel 友好型封装，为 Laravel 生态系统提供 AMQP 消息队列支持。

  - [Laravel](https://github.com/laravel/laravel)
  - [Lumen](https://github.com/laravel/lumen)
  - [Laravel Zero](https://github.com/laravel-zero/laravel-zero)

-----

## 安装

安装此包，请运行以下 Composer 命令：

```bash
composer require riven/laravel-xxljob
```

### Laravel

`XxlJob\Providers\XxlJobProvider::class` 服务提供者应该会自动注册。如果未自动注册，你可以手动将其添加到 `config/app.php` 的 `providers` 数组中：

```php
// config/app.php
'providers' => [
    // ... 
    XxlJob\Providers\XxlJobProvider::class,
]
```

### 发布配置文件：

```bash
php artisan vendor:publish --provider="XxlJob\Providers\XxlJobProvider"
```

### 初始化脚本「建议放在supervisor中，服务启动时执行」
```bash
# XXL-JOB 清除缓存和执行器初始化
php artisan xxljob:init
```
### 心跳保活「二选一即可」
- 维持心跳「本包使用 XXL-JOB 自动注册机制，需要维持心跳保活，建议放在supervisor中，服务启动时执行」
进程占用内存约90MB
```bash
# 发送心跳到XXL-JOB
php artisan xxljob:heartbeat
```
- 维持心跳「轻量级」
进程占用内存约30MB
```bash
# 发送心跳到XXL-JOB
php xxljob-heartbeat.php
```
