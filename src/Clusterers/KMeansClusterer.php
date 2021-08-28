<?php

namespace EmilKlindt\MarkerClusterer\Clusterers;

use League\Geotools\Geotools;
use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\BaseClusterer;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Exceptions\UnexpectedClusterCountChange;

class KMeansClusterer extends BaseClusterer
{
    /**
     * Temporary collection for caching of coordinates
     */
    private Collection $coordinates;

    /**
     * Geotools instance used for distance calculation
     */
    private Geotools $geotools;

    /**
     * Perform necessary setup of the algorithm
     */
    protected function setup(): void
    {
        $this->coordinates = new Collection();
        $this->geotools = new Geotools();
    }

    /**
     * Validate that the config is sufficient for the algorithm
     */
    public function validateConfig(): bool
    {
        return is_int($this->config->k)
            && is_int($this->config->iterations)
            && is_int($this->config->samples)
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
        $samples = new Collection();

        // retrive multiple clustering samples, from different
        // initial variables in order to lastly pick the sample
        // with the lowest sum of variance as result
        for ($s = 1; $s < $this->config->samples; $s++) {
            $this->solve();
            $samples->add($this->getClonedClusters());
        }

        // pick the sample with lowest variance as result
        $this->clusters = $samples
            ->sortBy(function (Collection $sample) {
                return $this->getSampleVariance($sample);
            })
            ->first();

        return $this->clusters;
    }

    /**
     * Iteratively cluster the markers from initially
     * random set of centroids
     */
    private function solve(): void
    {
        $this->randomlyAssignCentroids();

        // avoid further processing if maximum number of clusters
        // is higher than the number of markers provided
        if ($this->markers->count() <= $this->config->k) {
            return;
        }

        // repeat clustering until maximum iterations is reached
        // or the algorithm has converged (no longer changing)
        for ($i = 1; $i < $this->config->iterations; $i++) {
            $centroids = $this->getClusterCentroids();

            $this->assignMarkersToNearestCluster();
            $this->updateClusterCentroids();

            // avoid further processing if convergence is reached
            if ($this->hasConverged($centroids)) {
                break;
            }
        }
    }

    /**
     * Randomly select k distinct centroids
     */
    private function randomlyAssignCentroids(): void
    {
        $markers = $this->markers;

        // in case maximum clusters is larger than number of
        // markers, pick k random clusters as starting point
        if ($markers->count() > $this->config->k) {
            $markers = $markers->random($this->config->k);
        }

        $this->clusters = $markers
            ->map(function (Clusterable $marker) {
                return new Cluster([
                    'points' => new Collection(),
                    'centroid' => $marker->getClusterableCoordinate()
                ]);
            });
    }

    /**
     * Measure the distance for each marker to the centroids, and
     * assign each to the closest centroid/cluster
     */
    private function assignMarkersToNearestCluster(): void
    {
        $this->markers
            ->each(function (Clusterable $marker, int $index) {
                $this->clusters
                    ->min(function (Cluster $cluster) use ($index) {
                        $distance = $this->geotools
                            ->distance()
                            ->setFrom($cluster->centroid)
                            ->setTo($this->coordinates->get($index));

                        return $distance->{$this->config->distanceFormula};
                    })
                    ->markers
                    ->add($marker);
            });
    }

    /**
     * Calculate the mean of each clusters as new centroid
     */
    private function updateClusterCentroids(): void
    {
        $this->clusters
            ->each(function (Cluster $cluster) {
                $coordinates = $cluster->markers
                    ->map(function (Clusterable $marker) {
                        return $marker->getClusterableCoordinate();
                    });

                $cluster->centroid = new Coordinate([
                    $coordinates->avg('latitude'),
                    $coordinates->avg('longitude')
                ]);
            });
    }

    /**
     * Check whether the algorithm has reached convergence
     */
    private function hasConverged(Collection $centroids): bool
    {
        if ($this->clusters->count() !== $centroids->count()) {
            throw new UnexpectedClusterCountChange(
                "Number of clusters changed unexpectedly, from {$centroids->count()} to {$this->clusters->count()}"
            );
        }

        return $this->clusters
            ->every(function (Cluster $cluster, int $index) use ($centroids) {
                return $cluster->centroid == $centroids->get($index);
            });
    }

    /**
     * Retrieve collection of cluster centroids
     */
    private function getClusterCentroids(): Collection
    {
        return $this->clusters
            ->map(function (Cluster $cluster) {
                return $cluster->centroid;
            });
    }

    /**
     * Get the sum of variance for the clusters in sample
     */
    private function getSampleVariance(Collection $sample): float
    {
        // mean number of points
        $mean = $sample->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }) / $sample->count();

        // variance of points in clusters
        return $sample->sum(function (Cluster $cluster) use ($mean) {
            return pow($cluster->markers->count() - $mean, 2);
        }) / $sample->count();
    }
}
