<?php

use EmilKlindt\MarkerClustering\Algorithms\KMeansClustering;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Algorithm
    |--------------------------------------------------------------------------
    |
    | The default algorithm is used for clustering points in the clusterer, if
    | no specific algorithm is specified.
    |
    */

    'default_algorithm' => KMeansClustering::class,
];
