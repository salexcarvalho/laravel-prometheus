<?php

namespace Eudovic\PrometheusPHP\Services;

use Illuminate\Support\Facades\Config;

class AppMetrics
{
    private const METRICS_STORAGE_LOCAL = 'local';
    private const DB_QUERY_METRIC_KEY = 'db_query_execution_seconds';
    private const HTTP_REQUEST_METRIC_KEY = 'http_request_execution_seconds';

    public static function getAppMetrics(): void
    {
        self::collectAppMetrics();
    }

    public static function collectAppMetrics(): void
    {
        self::collectDbMetrics();
        self::collectRequestMetrics();

        if (self::isLocalStorageEnabled()) {
           self::clearLogsIfRequired();
        }
    }

    private static function collectDbMetrics(): void
    {
        if (!self::isMetricEnabled('db_query_performance')) {
            return;
        }
        $logContent = self::readLogs();
        if (!$logContent) {
            return;
        }

        echo self::formatDbMetrics($logContent['app'] ?? []);
    }

    private static function collectRequestMetrics(): void
    {
        if (!self::isMetricEnabled('http_request_performance')) {
            return;
        }

        $logContent = self::readLogs();
        if (!$logContent) {
            return;
        }

        echo self::formatRequestMetrics($logContent['app'] ?? []);
    }

    private static function readLogs(): ?array
    {
        if (!self::isLocalStorageEnabled()) {
            return null;
        }

        $logFile = config('prometheus.metrics_storage_options.local.path');
        if (!file_exists($logFile)) {
            return null;
        }

        $logContent = file_get_contents($logFile);
        return json_decode($logContent, true) ?: null;
    }

    private static function formatDbMetrics(array $logContent): string
    {
        return self::formatMetrics(
            $logContent,
            self::DB_QUERY_METRIC_KEY,
            function ($metric) {
                return $metric['params']['query'];
            },
            "\n # HELP db_query_execution_seconds Tempo de execução das consultas SQL",
            "# TYPE db_query_execution_seconds summary"
        );
    }

    private static function formatRequestMetrics(array $logContent): string
    {
        return self::formatMetrics(
            $logContent,
            self::HTTP_REQUEST_METRIC_KEY,
            function ($metric) {
                return $metric['params']['path'] . ' ' . $metric['params']['method'];
            },
            "\n # HELP http_request_execution_seconds Tempo de execução das requisições HTTP",
            "# TYPE http_request_execution_seconds summary"
        );
    }

    private static function formatMetrics(
        array $logContent,
        string $metricKey,
        callable $groupingKeyCallback,
        string $helpMessage,
        string $typeMessage
    ): string {
        $metrics = array_filter($logContent, fn($item) => isset($item['key']) && $item['key'] === $metricKey);
        if (empty($metrics)) {
            return '';
        }

        $groupedMetrics = [];
        foreach ($metrics as $metric) {
            $groupKey = $groupingKeyCallback($metric);
            $groupedMetrics[$groupKey][] = (float) $metric['value'];
        }

        $output = [$helpMessage, $typeMessage];
        foreach ($groupedMetrics as $key => $values) {
            $output = array_merge($output, self::generateMetricsOutput($metricKey, $key, $values));
        }
        return implode("\n", $output);
    }

    private static function generateMetricsOutput(string $metricKey, string $key, array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $quantiles = self::calculateQuantiles($values, [0.5, 0.9, 0.99]);
        $sum = array_sum($values);
        $count = count($values);

        $output = [];
        foreach ($quantiles as $quantile) {
            $output[] = sprintf(
                "%s{key=\"%s\", quantile=\"%s\"} %s",
                $metricKey,
                $key,
                $quantile['quantile'],
                $quantile['value']
            );
        }

        $output[] = sprintf("%s_sum{key=\"%s\"} %s", $metricKey, $key, $sum);
        $output[] = sprintf("%s_count{key=\"%s\"} %s", $metricKey, $key, $count);

        return $output;
    }

    private static function calculateQuantiles(array $values, array $quantiles): array
    {
        sort($values);
        $results = [];
        $count = count($values);

        foreach ($quantiles as $quantile) {
            $index = (int) floor($quantile * ($count - 1));
            $results[] = ['quantile' => $quantile, 'value' => $values[$index]];
        }

        return $results;
    }

    private static function clearLogsIfRequired(): void
    {
        $logFile = config('prometheus.metrics_storage_options.local.path');
        if (!file_exists($logFile)) {
            return;
        }

        file_put_contents($logFile, json_encode(['app' => []]));
    }

    private static function isMetricEnabled(string $metric): bool
    {
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        return $metricsEnabled[$metric] ?? false;
    }

    private static function isLocalStorageEnabled(): bool
    {
        return config('prometheus.metrics_storage') === self::METRICS_STORAGE_LOCAL;
    }
}
