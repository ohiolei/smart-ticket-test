<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     */

    protected function configureRateLimiting()
    {
        RateLimiter::for('classify', function ($request) {
            return [
                Limit::perMinute(env('RATE_PER_MINUTE', 10))->by($request->ip()),
            ];
        });
    }
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            require base_path('routes/api.php');
        });
    }


}
