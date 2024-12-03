<?php

namespace Eudovic\PrometheusPHP\Http\Middleware;

use Closure;
use Eudovic\PrometheusPHP\Metrics\Logs\LogMetrics;
use Illuminate\Support\Facades\Log;

class LogRequestMetrics
{
    public function handle($request, Closure $next)
    {
        if (!$this->isHttpRequest($request)) {
            return $next($request);
        }

        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $path = $request->path();
        $method = $request->method();
        $status = $response->getStatusCode();

        LogMetrics::log(
            config('prometheus.metrics_storage'),
            'summary',
            'http_request_execution_seconds',
            $executionTime,
            [
                'path' => $path,
                'method' => $method,
                'status' => $status,
            ]
        );

        return $response;
    }

    protected function isHttpRequest($request): bool
    {
        return $request instanceof \Illuminate\Http\Request;
    }
}
