<?php

namespace EmilKlindt\MarkerClusterer\Clusterers;

use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\BaseClusterer;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;

class DensityBasedSpatialClusterer extends BaseClusterer
{
    /**
     * Array to keep track of visited nodes, by index
     */
    private Collection $visited;

    /**
     * Temporary collection for caching of coordinates.
     */
    private Collection $coordinates;

    /**
     * Merge the provided config with default values.
     */
    protected function mergeDefaultConfig(): void
    {
        $this->setDefaultConfigValue('distanceFormula', config('marker-clusterer.default_distance_formula'));
        $this->setDefaultConfigValue('includeNoise', config('marker-clusterer.dbscan.default_include_noise'));
    }

    /**
     * Perform necessary setup of the algorithm.
     */
    protected function setup(): void
    {
        $this->coordinates = new Collection();
    }

    /**
     * Validate that the config is sufficient for the algorithm
     */
    protected function validateConfig(): bool
    {
        return is_float($this->config->epsilon)
            && is_int($this->config->minSamples)
            && is_bool($this->config->includeNoise)
            && in_array($this->config->distanceFormula, DistanceFormula::getConstants());
    }

    /**
     * Add a new marker to the clusterer
     */
    public function addMarker(Clusterable $marker): self
    {
        $this->markers->add($marker);
        $this->coordinates->add($marker->getClusterableCoordinate());

        return $this;
    }

    /**
     * Get the clusters derived from the added markers
     */
    public function getClusters(): Collection
    {
        $this->clearVisited();

        $noise = new Collection();

        // visit each point and expand clusters meeting sample criterion
        $this->markers
            ->each(function (Clusterable $marker, int $p) use ($noise) {
                if ($this->visited->contains($p)) {
                    return;
                }

                $neighborhoodIndexes = $this->getIndexesWithinNeighborhood($p);

                if ($neighborhoodIndexes->count() >= $this->config->minSamples) {
                    $this->expandClusterNeighborhood($neighborhoodIndexes);
                } else {
                    $this->visited->push($p);
                    $noise->push($p);
                }
            });

        // create indvidual clusters for noise, if included
        if ($this->config->includeNoise) {
            $noise
                ->each(function (int $p) {
                    $this->clusters->push(new Cluster([
                        'markers' => new Collection([
                            $this->markers->get($p)
                        ])
                    ]));
                });
        }

        $this->updateClusterCentroids();

        return $this->clusters;
    }

    /**
     * Continously consider all points within epsilon as part of the
     * cluster, untill no more points are within epsilon distance.
     */
    private function expandClusterNeighborhood(Collection $queue): void
    {
        $clusterIndexes = new Collection();

        while (!$queue->isEmpty()) {
            $p = $queue->pop();

            if ($this->visited->contains($p)) {
                continue;
            }

            $this->visited->push($p);
            $clusterIndexes->push($p);

            $queue->push(...$this->getIndexesWithinNeighborhood($p));
        }

        $this->createClusterFromIndexes($clusterIndexes);
    }

    /**
     * Create a new cluster from a collection of marker indexes
     */
    private function createClusterFromIndexes(Collection $indexes): void
    {
        $markers = $indexes
            ->map(function (int $index) {
                return $this->markers->get($index);
            });

        $this->clusters
            ->push(new Cluster([
                'markers' => $markers
            ]));
    }

    /**
     * Reset visited nodes, from previous runs
     */
    private function clearVisited(): void
    {
        $this->visited = new Collection();
    }

    /**
     * Get index markers within epsilon distance of marker index
     */
    private function getIndexesWithinNeighborhood(int $index): Collection
    {
        $origin = $this->coordinates->get($index);

        return $this->coordinates
            ->filter(function (Coordinate $coordinate) use ($origin) {
                return $this->distanceCalculator->measure($origin, $coordinate)
                    < $this->config->epsilon;
            })
            ->map(function (Coordinate $coordinate, int $index) {
                return $index;
            });
    }
}
