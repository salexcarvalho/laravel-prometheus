<?php

namespace Eudovic\PrometheusPHP\Providers;

use Eudovic\PrometheusPHP\Metrics\Logs\LocalLogs;
use Eudovic\PrometheusPHP\Metrics\Logs\LogMetrics;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class PrometheusServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/prometheus.php',
            'prometheus'
        );

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Eudovic\PrometheusPHP\Exceptions\Handler::class
        );
    }

    public function boot()
    {
        $this->app['router']->aliasMiddleware('auth.metric', \Eudovic\PrometheusPHP\Http\Middleware\AuthMetricMiddleware::class);

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Eudovic\PrometheusPHP\Http\Middleware\LogRequestMetrics::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Eudovic\PrometheusPHP\Console\LocalLogFileVerificationCommand::class,
                \Eudovic\PrometheusPHP\Console\CreateMetricsTokenCommand::class,
            ]);
        }

        if (config('prometheus.enable_auth_route')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }


        $this->publishes([
            __DIR__ . '/../config/prometheus.php' => config_path('prometheus.php'),
        ], 'prometheus-php-config');

        $this->dbListener();

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function dbListener()
    {
        DB::listen(function (QueryExecuted $query) {
            $executionTime = $query->time / 1000;
            $querySql = $query->sql;
            $querySql = str_replace('"', "'", $querySql);
            $connectionName = $query->connectionName;
            LogMetrics::log(config('prometheus.metrics_storage'), 'summary', 'db_query_execution_seconds', $executionTime, ['query' => $querySql, 'connection' => $connectionName]);
        });
    }
}
