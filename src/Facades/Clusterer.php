<?php

namespace EmilKlindt\MarkerClustering\Facades;

use Illuminate\Support\Facades\Facade;

class Clusterer extends Facade
{
    /**
     * Get the registered name of the component.
     */
    public static function getFacadeAccessor(): string
    {
        return 'clusterer';
    }
}
