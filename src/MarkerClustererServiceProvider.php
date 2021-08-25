<?php

namespace EmilKlindt\MarkerClusterer;

use Illuminate\Support\ServiceProvider;

class MarkerClustererServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/marker-clusterer.php' => config_path('marker-clusterer.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/marker-clusterer.php', 'marker-clusterer');
    }
}
