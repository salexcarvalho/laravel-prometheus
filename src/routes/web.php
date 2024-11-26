<?php

use Eudovic\PrometheusPHP\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth.metric'])->group(function () {
    Route::get('/metrics', MetricsController::class);
});
