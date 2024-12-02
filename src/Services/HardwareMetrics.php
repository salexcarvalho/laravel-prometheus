<?php

namespace Eudovic\PrometheusPHP\Services;

use Eudovic\PrometheusPHP\Metrics\Types\Gauge;
use Illuminate\Support\Facades\Config;

if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg()
    {
        return [0, 0, 0];
    }
}
class HardwareMetrics
{

    public static function getCPUMetrics()
    {

        $metrics = '';
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $cpuLoad = sys_getloadavg();

        if ($metricsEnabled['system_cpu_load_1m']) {
            $metrics .= Gauge::addMetric('system_cpu_load_1m', $cpuLoad[0], 'Carga da CPU nos últimos 1 minuto');
        }
        if ($metricsEnabled['system_cpu_load_5m']) {
            $metrics .= Gauge::addMetric('system_cpu_load_5m', $cpuLoad[1], 'Carga da CPU nos últimos 5 minutos');
        }
        if ($metricsEnabled['system_cpu_load_15m']) {
            $metrics .= Gauge::addMetric('system_cpu_load_15m', $cpuLoad[2], 'Carga da CPU nos últimos 15 minutos');
        }

        return $metrics;
    }

    public static function getMemoryMetrics()
    {
        $metrics = '';
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $memoryUsage = memory_get_usage();

        if ($metricsEnabled['system_memory_usage_bytes']) {
            $metrics .= Gauge::addMetric('system_memory_usage_bytes', $memoryUsage, 'Uso de memória pelo PHP em bytes');
        }
    }

    public static function getDiskMetrics()
    {

        $metrics = '';
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $diskFree = disk_free_space("/");
        $diskTotal = disk_total_space("/");

        if ($metricsEnabled['system_disk_free_bytes']) {
            $metrics .= Gauge::addMetric('system_disk_free_bytes', $diskFree, 'Espaço livre em disco em bytes');
        }
        if ($metricsEnabled['system_disk_total_bytes']) {
            $metrics .= Gauge::addMetric('system_disk_total_bytes', $diskTotal, 'Espaço total em disco em bytes');
        }
    }
}
