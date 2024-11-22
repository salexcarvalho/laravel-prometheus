<?php

namespace Eudovic\PrometheusPHP\Metrics\Types;


class Gauge
{
    public static function metric(string $name, string|int $value, string $label)
    {
        $metricString = "# HELP {$name} {$label}\n";
        $metricString .= "# TYPE {$name} gauge\n";
        $metricString .= "{$name} {$value}\n";

        echo $metricString."\n";
    }
}