<?php

namespace Eudovic\PrometheusPHP\Services;

use Eudovic\PrometheusPHP\Metrics\Types\Counter;
use Eudovic\PrometheusPHP\Metrics\Types\Gauge;
use Illuminate\Support\Facades\Config;

class HardwareMetrics
{
    
    public static function getCPUMetrics()
    {
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $cpuLoad = sys_getloadavg();

        if ($metricsEnabled['system_cpu_load_1m']) {
            Gauge::metric('system_cpu_load_1m', $cpuLoad[0], 'Carga da CPU nos últimos 1 minuto');
        }
        if ($metricsEnabled['system_cpu_load_5m']) {
            Gauge::metric('system_cpu_load_5m', $cpuLoad[1], 'Carga da CPU nos últimos 5 minutos');
        }
        if ($metricsEnabled['system_cpu_load_15m']) {
            Gauge::metric('system_cpu_load_15m', $cpuLoad[2], 'Carga da CPU nos últimos 15 minutos');
        }
    }

    public static function getMemoryMetrics()
    {
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $memoryUsage = memory_get_usage();

        if ($metricsEnabled['system_memory_usage_bytes']) {
            Gauge::metric('system_memory_usage_bytes', $memoryUsage, 'Uso de memória pelo PHP em bytes');
        }
    }

    public static function getDiskMetrics()
    {
        $metricsEnabled = Config::get('prometheus.metrics_enabled');
        $diskFree = disk_free_space("/");
        $diskTotal = disk_total_space("/");

        if ($metricsEnabled['system_disk_free_bytes']) {
            Gauge::metric('system_disk_free_bytes', $diskFree, 'Espaço livre em disco em bytes');
        }
        if ($metricsEnabled['system_disk_total_bytes']) {
            Gauge::metric('system_disk_total_bytes', $diskTotal, 'Espaço total em disco em bytes');
        }
    }

   
}