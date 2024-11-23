<?php

namespace Eudovic\PrometheusPHP\Metrics\Types;

use Eudovic\PrometheusPHP\Abstracts\AbstractMetric;
use Eudovic\PrometheusPHP\Models\Message;

class Gauge extends AbstractMetric
{
    const METRIC_TYPE = 'gauge';
    
    public static function addMetric(string $name, string|int $value, string $label)
    {
        $instance = new self();
        $message = new Message();
        $message->setMessage($name, [], $value);
        $instance->metric(self::METRIC_TYPE, $name, $label, [$message]);
    }
}
