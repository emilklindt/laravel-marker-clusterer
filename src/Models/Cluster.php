<?php

namespace EmilKlindt\MarkerClustering\Models;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use EmilKlindt\MarkerClustering\Models\Coordinate;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;

class Cluster extends DataTransferObject
{
    /**
     * List of points in this cluster
     */
    public Collection $points;

    /**
     * The center point of the cluster
     */
    public Coordinate $center;
}
