<?php

declare(strict_types=1);

namespace XxlJob\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use XxlJob\Exception\InvalidTokenException;

class AuthMiddleware {
    public function handle(Request $request, Closure $next): Response {
        $configToken = config('xxljob.access_token');
        if (!$configToken) {
            return $next($request);
        }
        $token = $request->header('XXL-JOB-ACCESS-TOKEN');
        if ($token !== $configToken) {
            Log::error('xxl-job access_token verification failed');
            throw new InvalidTokenException('xxl-job access_token verification failed');
        }
        return $next($request);
    }
}
