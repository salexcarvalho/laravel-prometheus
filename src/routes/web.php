<?php

use Eudovic\PrometheusPHP\Http\Controllers\MetricsController;
use Eudovic\PrometheusPHP\Metrics\Types\Gauge;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/metrics', MetricsController::class);

