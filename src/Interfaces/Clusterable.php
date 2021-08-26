<?php

namespace EmilKlindt\MarkerClustering\Interfaces;

use EmilKlindt\MarkerClustering\Models\Coordinate;

interface Clusterable
{
    /**
     * Get the latitude/longitude coordinate of the point
     */
    function getClusterableCoordinate(): Coordinate;
}
