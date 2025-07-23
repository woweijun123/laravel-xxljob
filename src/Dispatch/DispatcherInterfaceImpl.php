<?php

namespace XxlJob\Dispatch;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Log;

class DispatcherInterfaceImpl implements DispatcherInterface
{
    const string HEADER = 'XXL-JOB-ACCESS-TOKEN';

    /**
     * @var string
     */
    private string $server;

    /**
     * @var string
     */
    private string $accessToken;

    public function __construct(string $server, string $accessToken)
    {
        $this->server = $server;
        $this->accessToken = $accessToken;
    }

    public function loopRegistry(string $registryKey, string $registryValue, int $loop = 60): void
    {
        while ($loop-- > 0) {
            $this->registry($registryKey, $registryValue);
            sleep(1);
        }
    }

    public function registry(string $registryKey, string $registryValue): bool
    {
        $url = "{$this->server}/api/registry";
        $body = [
            'registryGroup' => 'EXECUTOR',
            'registryKey' => $registryKey,
            'registryValue' => $registryValue,
        ];
        $guzzle = new Client();
        Log::info("执行器注册请求 url={$url} accessToken={$this->accessToken} body=" . json_encode($body));
        $respStr = $guzzle->post($url, [
            RequestOptions::JSON => $body,
            RequestOptions::HEADERS => [
                self::HEADER => $this->accessToken,
            ]
        ])
            ->getBody()
            ->getContents();
        Log::debug("执行器注册响应: {$respStr}");
        $response = Json::decode($respStr);
        if ($response->code == 200) {
            Log::info('执行器注册成功');
            return true;
        } else {
            Log::error('执行器注册失败');
            return false;
        }
    }

    public function callback()
    {
    }

    public function registryRemove(string $registryKey, string $registryValue)
    {
    }
}
