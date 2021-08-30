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
     * Matrix (2-dimensional array) of distances from marker to marker
     */
    private Collection $distanceMatrix;

    /**
     * Temporary collection for caching of coordinates.
     */
    private Collection $coordinates;

    /**
     * Merge the provided config with default values.
     */
    protected function mergeDefaultConfig(): void
    {
        $this->setDefaultConfig('includeNoise', config('marker-clusterer.dbscan.default_include_noise'));
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
    public function addMarker(Clusterable $marker): void
    {
        $this->markers->add($marker);
        $this->coordinates->add($marker->getClusterableCoordinate());
    }

    /**
     * Get the clusters derived from the added markers
     */
    public function getClusters(): Collection
    {
        $this->clearVisited();
        $this->setDistanceMatrix();

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
     * Calculate and set values for distance matrix
     */
    private function setDistanceMatrix(): void
    {
        $this->distanceMatrix = new Collection();

        // calculate distance matrix
        $this->coordinates
            ->each(function (Coordinate $coordinate, int $y) {
                $this->distanceMatrix->put($y, new Collection());

                for ($x = 0; $x <= $y; $x++) {
                    $this->distanceMatrix->get($y)->put(
                        $x,
                        $this->distanceCalculator
                            ->measure($coordinate, $this->coordinates->get($x))
                    );
                }
            });

        // diagonally mirror matrix, for faster read access
        $this->markers
            ->each(function (Clusterable $marker, int $y) {
                for ($x = $y + 1; $x < $this->markers->count(); $x++) {
                    $this->distanceMatrix->get($y)->put(
                        $x,
                        $this->distanceMatrix
                            ->get($x)
                            ->get($y)
                    );
                }
            });
    }

    /**
     * Get index markers within epsilon distance of marker index
     */
    private function getIndexesWithinNeighborhood(int $index): Collection
    {
        return $this->distanceMatrix
            ->get($index)
            ->filter(function (float $distance) {
                return $distance < $this->config->epsilon;
            })
            ->map(function (float $distance, int $index) {
                return $index;
            });
    }
}
