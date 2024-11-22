<?php

namespace Eudovic\PrometheusPHP\Metrics\Logs;

class LogMetrics
{
    public static function log(string $storage = 'local', string $type, string $name, string|float $value, array $params = [])
    {
        if ($storage == 'local') {
            LocalLogs::log($type, $name, $value,  $params);
            return;
        }

        if ($storage == 'redis') {
            //not implemented yet
        }
    }
}
