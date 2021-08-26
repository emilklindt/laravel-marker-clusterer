<?php

namespace EmilKlindt\MarkerClustering;

use Illuminate\Support\ServiceProvider;

class ClustererServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/clusterer.php' => config_path('clusterer.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/clusterer.php',
            'courier'
        );
    }
}
