<?php

namespace EmilKlindt\MarkerClusterer\Interfaces;

use League\Geotools\Coordinate\Coordinate;

interface Clusterable
{
    /**
     * Get the latitude/longitude coordinate of the point.
     */
    function getClusterableCoordinate(): Coordinate;
}
