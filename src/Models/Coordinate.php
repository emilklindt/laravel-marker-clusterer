<?php

namespace EmilKlindt\MarkerClustering\Models;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;

class Coordinate extends DataTransferObject
{
    /**
     * The latitude of the coordinate
     */
    public float $latitude;

    /**
     * The longitude of the coordinate
     */
    public float $longitude;
}
