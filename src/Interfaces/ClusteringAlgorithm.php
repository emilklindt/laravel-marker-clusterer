<?php

namespace EmilKlindt\MarkerClustering\Interfaces;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClustering\Models\Config;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;

interface ClusteringAlgorithm
{
    /**
     * Add new point
     */
    function addPoint(Collection $points, Collection $clusters, Clusterable $point): void;

    /**
     * Solve clusters for points and return
     */
    function getClusters(Collection $points, Collection $clusters): Collection;
}
