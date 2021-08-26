<?php

namespace EmilKlindt\MarkerClustering\Models;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use EmilKlindt\MarkerClustering\Models\Coordinate;

class Config extends DataTransferObject
{
    /**
     * Max number of clusters, or zero for no limit
     */
    public int $k;

}
