<?php

namespace Eudovic\PrometheusPHP\Http\Controllers;

use Eudovic\PrometheusPHP\Services\AppMetrics;
use Eudovic\PrometheusPHP\Services\HardwareMetrics;
use Illuminate\Support\Facades\Config;

class MetricsController
{
    public function __invoke()
    {
        $prometheusStagesEnabled = Config::get('prometheus.stages_enabled');
        $currentStage = Config::get('app.env');

        if (!isset($prometheusStagesEnabled[$currentStage]) || !$prometheusStagesEnabled[$currentStage]) {
            return response('Metrics not enabled for this stage', 403);
        }

        $metrics = $this->standarMetrics();
        return response($metrics, 200)
            ->header('Content-Type', 'text/plain');
    }

    private function standarMetrics()
    {
        $metrics = '';
        // Hardware metrics
        $metrics .= HardwareMetrics::getCPUMetrics();
        $metrics .= HardwareMetrics::getMemoryMetrics();
        $metrics .= HardwareMetrics::getDiskMetrics();

        // // App metrics
        $metrics .= AppMetrics::collectMetrics();

        return $metrics;
    }
}
