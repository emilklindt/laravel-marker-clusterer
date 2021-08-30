<?php

namespace EmilKlindt\MarkerClusterer;

use Illuminate\Support\ServiceProvider;
use EmilKlindt\MarkerClusterer\Clusterers\KMeansClusterer;
use EmilKlindt\MarkerClusterer\Clusterers\DensityBasedSpatialClusterer;

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

        $this->app->bind('density-based-spatial-clusterer', function () {
            return new DensityBasedSpatialClusterer();
        });

        $this->app->bind('k-means-clusterer', function () {
            return new KMeansClusterer();
        });
    }
}
