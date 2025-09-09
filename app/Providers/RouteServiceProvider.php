<?php

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

RateLimiter::for('classify', function ($request) {
    $perMin = (int) env('RATE_PER_MINUTE', 10);
    return [Limit::perMinute($perMin)->by($request->ip())];
});