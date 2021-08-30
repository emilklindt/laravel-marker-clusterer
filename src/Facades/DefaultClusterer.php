<?php

namespace EmilKlindt\MarkerClusterer\Facades;

use Illuminate\Support\Facades\Facade;

class DefaultClusterer extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return config('marker-clusterer.default_clusterer');
    }
}
