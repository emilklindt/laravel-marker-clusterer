<?php

namespace EmilKlindt\MarkerClusterer\Models;

use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;
use Spatie\DataTransferObject\DataTransferObject;

class Cluster extends DataTransferObject
{
    /**
     * List of points in this cluster.
     */
    public Collection $markers;

    /**
     * The center point of the cluster.
     */
    public ?Coordinate $centroid;
}
