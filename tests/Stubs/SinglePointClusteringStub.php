<?php

namespace EmilKlindt\MarkerClustering\Tests\Stubs;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClustering\Models\Cluster;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;
use EmilKlindt\MarkerClustering\Traits\ClusteringAlgorithm;

class SinglePointClusteringStub
{
    use ClusteringAlgorithm;

    /**
     * Add new point.
     *
     * @param Collection $points
     * @param Collection $clusters
     * @param Clusterable $point
     * @return void
     */
    public function addPoint(Collection $points, Collection $clusters, Clusterable $point): void
    {
        $clusters->add(new Cluster([
            'points' => collect($point),
            'center' => $point->getClusterableCoordinate()
        ]));
    }

    /**
     * Solve clusters for points and return.
     *
     * @return Collection<Cluster>
     */
    public function getClusters(Collection $points, Collection $clusters): Collection
    {
        return $clusters;
    }
}
