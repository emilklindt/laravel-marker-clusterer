<?php

namespace EmilKlindt\MarkerClusterer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method \Illuminate\Support\Collection getClusters()
 * @method self setConfig(\EmilKlindt\MarkerClusterer\Models\Config $config)
 * @method self addMarker(\EmilKlindt\MarkerClusterer\Interfaces\Clusterable $marker)
 *
 * @see \EmilKlindt\MarkerClusterer\Clusterers
 */
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
