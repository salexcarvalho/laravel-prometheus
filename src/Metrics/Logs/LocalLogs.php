<?php
namespace Eudovic\PrometheusPHP\Metrics\Logs;

class LocalLogs {
    public static function log(string $type = 'gauge', string $key, string $value, array $params = []) {

        $filePath = storage_path('logs/query_log.json');
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([]));
        }
       
        $logData = json_decode(file_get_contents($filePath), true);
        $logData['app'][] = [
            'type' => $type,     
            'key' => $key,     
            'value' => $value,
            'params' => $params,
        ];
        file_put_contents($filePath, json_encode($logData));
    }

    public static function path() {
        return config('prometheus.metrics_storage_options.local.path');
    }
}