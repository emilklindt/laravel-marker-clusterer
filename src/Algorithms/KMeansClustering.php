<?php

namespace EmilKlindt\MarkerClustering\Algorithms;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;
use EmilKlindt\MarkerClustering\Traits\ConfigurableAlgorithm;
use EmilKlindt\MarkerClustering\Interfaces\ClusteringAlgorithm;

class KMeansClustering implements ClusteringAlgorithm
{
    use ConfigurableAlgorithm;

    /**
     * Add new point
     */
    public function addPoint(Collection $points, Collection $clusters, Clusterable $point): void
    {

    }

    /**
     * Solve clusters for points and return
     */
    public function getClusters(Collection $points, Collection $clusters): Collection
    {

    }
}
