<?php

namespace XxlJob\Api;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;

class BaseClient
{
    const string API_URI = '';

    /**
     * @var array 基本配置
     */
    protected array $config = [
        'uri'         => '${API_URI}${method}',
        'type'        => 'POST',
        'headers'     => [
            'Accept' => 'application/json',
        ],
        'format'      => 'json_encode', // 自动格式化 POST 数据
        'decode'      => true,          // 是否解析返回数据
        'returnField' => null,          // 默认返回字段，null 返回整个数组
        'errKey'      => null,          // 判断错误请求的键
        'errVal'      => null,          // 判断错误请求的值
        'errMsg'      => '',            // 错误提示信息取值
        'throw'       => true,          // 是否直接抛出异常
        'onError'     => null,          // 错误回调
    ];

    /**
     * @var array 扩展类使用的扩展配置
     */
    protected array $configExt = [];

    protected function getConfig($setting): array
    {
        if (is_string($setting)) {
            $setting = ['method' => $setting];
        }
        $accessToken = config('access_token');
        if ($accessToken) {
            Arr::set($setting, 'headers.XXL-JOB-ACCESS-TOKEN', $accessToken);
        }
        return array_replace_recursive($this->config, config('xxl_job'), $this->configExt, $setting);
    }

    /**
     * 发送请求
     * @param array|string $setting 配置
     * @param array        $post    POST数据
     * @return mixed
     * @throws
     */
    protected function send(array|string $setting, array $post = []): mixed
    {
        $conf = $this->getConfig($setting);
        $uri  = $conf['uri'] = $this->parse($conf['uri'], $conf);

        $option = [
            'headers' => $conf['headers'] ? $this->parse($conf['headers'], $conf,) : [],
        ];

        if ($post) {
            $post = $this->parse($post, $conf);
            if (strtoupper($conf['type']) == 'GET') {
                $uri .= (strpos($uri, '?') ? '&' : '?') . http_build_query($post);
            } elseif (!empty($conf['multipart'])) {
                foreach ($post as $key => $val) {
                    $conf['multipart'][] = [
                        'name'     => $key,
                        'contents' => $val,
                    ];
                }
            } else {
                $option['json'] = $post;
            }
        }

        // multipart/form-data 及 x-www-form-urlencoded 表单
        $option += Arr::only($conf, ['multipart', 'form_params']);
        $response = $this->request($conf['type'], $uri, $option, $exception);

        //不用解析返回数据
        if (!$conf['decode']) {
            return $response;
        }

        $res = json_decode($response, true);

        // 异常处理
        if ($conf['throw'] !== false && ($exception || $this->isThrow($res, $conf))) {
            // 配置有错误回调
            if (is_callable(Arr::get($conf, 'onError'))) {
                $temp = $this->call($conf['onError'], [$res, $conf, func_get_args()]);
                if ($temp !== false) {
                    return $temp;
                }
            }
            $this->exception(Arr::get($res, $conf['errMsg'], ''), $res);
        }

        return Arr::get($res, $conf['returnField']);
    }

    /**
     * http 请求
     * @param string $type   请求类型
     * @param string $uri    请求地址
     * @param array  $option 选项
     * @param bool   $exception
     * @return string
     * @throws
     */
    protected function request(string $type, string $uri = '', array $option = [], ?bool &$exception = false): string
    {
        try {
             /* @var Client $client */
            $client = app(Client::class)->create();
            $response = $client->request($type, $uri, $option)->getBody();
        } catch (RequestException $e) {
            $exception = true;
            $response  = $e->getResponse()->getBody();
        }

        return (string)$response;
    }

    protected function parse($string, $conf = [])
    {
        if (is_array($string)) {
            foreach ($string as &$line) {
                $line = $this->parse($line, $conf);
            }

            return $string;
        }

        if (!is_string($string)) {
            return $string;
        }

        return preg_replace_callback('/\${([^{}]*)}/', function ($match) use ($conf) {
            $match = $match[1];
            return match ($match) {
                'API_URI' => static::API_URI,
                isset($conf[$match]) ? $match : '' => $conf[$match],
                default => (function () use ($conf, $match) {
                    $key = str_replace(['.', '#'], ['/', '.'], $match);
                    $key = explode('/', $key);
                    $prefix = array_shift($key);

                    return match ($prefix) {
                        'data' => $this->getData(...$key),
                        'conf' => Arr::get($conf, implode('.', $key)),
                        default => ''
                    };
                })()
            };
        }, $string);
    }

    protected function isThrow($res, $conf): bool
    {
        if (is_callable($conf['throw'])) {
            return $this->call($conf['throw'], [$res, $conf]);
        }

        // 有配置判断错误请求的值，那判断响应 key 是否和错误值相等，否则只需要有错误 key 就错有错
        if (!empty($conf['errKey']) && (is_null(Arr::get($conf, 'errVal')) ? Arr::get($res, $conf['errKey']) : Arr::get($res, $conf['errKey']) == $conf['errVal'])) {
            return true;
        }

        return !empty($conf['successKey']) && Arr::get($res, $conf['successKey']) != $conf['successVal'];
    }

    protected function getData(string $method, ...$args): mixed
    {
        return compact('method', 'args');
    }

    /**
     * @throws ApiException
     */
    protected function exception($message)
    {
        throw new ApiException($message);
    }

    /**
     * 调用回调函数
     * @param $callback
     * @param array $args
     * @return mixed
     */
    protected function call($callback, array $args = []): mixed
    {
        $result = null;
        if ($callback instanceof Closure) {
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}
