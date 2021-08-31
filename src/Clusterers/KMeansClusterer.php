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
     * Temporary collection for caching of coordinates.
     */
    private Collection $coordinates;

    /**
     * Merge the provided config with default values.
     */
    protected function mergeDefaultConfig(): void
    {
        $this->setDefaultConfigValue('samples', config('marker-clusterer.k_means.default_maximum_samples'));
        $this->setDefaultConfigValue('iterations', config('marker-clusterer.k_means.default_maximum_iterations'));
        $this->setDefaultConfigValue('distanceFormula', config('marker-clusterer.k_means.default_distance_formula'));
        $this->setDefaultConfigValue('convergenceMaximum', config('marker-clusterer.k_means.default_convergence_maximum'));
    }

    /**
     * Perform necessary setup of the algorithm.
     */
    protected function setup(): void
    {
        $this->coordinates = new Collection();
    }

    /**
     * Validate that the config is sufficient for the algorithm.
     */
    public function validateConfig(): bool
    {
        return is_int($this->config->k)
            && is_int($this->config->iterations)
            && is_int($this->config->samples)
            && is_float($this->config->convergenceMaximum)
            && in_array($this->config->distanceFormula, DistanceFormula::getConstants());
    }

    /**
     * Add a new marker to the clusterer.
     */
    public function addMarker(Clusterable $marker): self
    {
        $this->markers->add($marker);
        $this->coordinates->add($marker->getClusterableCoordinate());

        return $this;
    }

    /**
     * Get the clusters derived from the added markers.
     */
    public function getClusters(): Collection
    {
        if ($this->markers->count() === 0) {
            return new Collection();
        }

        $samples = new Collection();

        // retrive multiple clustering samples, from different
        // initial variables in order to lastly pick the sample
        // with the lowest sum of variance as result
        for ($s = 1; $s <= $this->config->samples; $s++) {
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
     * random set of centroids.
     */
    private function solve(): void
    {
        $this->randomlyAssignCentroids();

        // avoid further processing if maximum number of clusters
        // is higher than the number of markers provided
        if ($this->markers->count() <= $this->config->k) {
            $this->assignMarkersToNearestCluster();
            return;
        }

        // repeat clustering until maximum iterations is reached
        // or the algorithm has converged (no longer changing)
        for ($i = 1; $i <= $this->config->iterations; $i++) {
            $centroids = $this->getClusterCentroids();

            $this->clearAssignedClusterMarkers();
            $this->assignMarkersToNearestCluster();
            $this->updateClusterCentroids();

            // avoid further processing if convergence is reached
            if ($this->hasConverged($centroids)) {
                break;
            }
        }
    }

    /**
     * Randomly select k distinct centroids.
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
                    'markers' => new Collection(),
                    'centroid' => $marker->getClusterableCoordinate()
                ]);
            });
    }

    /**
     * Remove markers from clusters they were previously assigend to.
     */
    private function clearAssignedClusterMarkers(): void
    {
        $this->clusters
            ->each(function (Cluster $cluster) {
                $cluster->markers = new Collection();
            });
    }

    /**
     * Measure the distance for each marker to the centroids, and
     * assign each to the closest centroid/cluster.
     */
    private function assignMarkersToNearestCluster(): void
    {
        $this->markers
            ->each(function (Clusterable $marker, int $index) {
                $this->clusters
                    ->sortBy(function (Cluster $cluster) use ($index) {
                        return $this->distanceCalculator
                            ->measure($cluster->centroid, $this->coordinates->get($index));
                    })
                    ->first()
                    ->markers
                    ->add($marker);
            });
    }

    /**
     * Check whether the algorithm has reached convergence.
     */
    private function hasConverged(Collection $centroids): bool
    {
        return $this->clusters
            ->every(function (Cluster $cluster, int $index) use ($centroids) {
                return $this->distanceCalculator->measure($cluster->centroid, $centroids->get($index))
                    <= $this->config->convergenceMaximum;
            });
    }

    /**
     * Retrieve collection of cluster centroids.
     */
    private function getClusterCentroids(): Collection
    {
        return $this->clusters
            ->map(function (Cluster $cluster) {
                return $cluster->centroid;
            });
    }

    /**
     * Get the sum of variance for the clusters in sample.
     */
    private function getSampleVariance(Collection $sample): float
    {
        // mean number of markers per cluster
        $mean = $sample->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }) / $sample->count();

        // variance of number of markers in clusters
        return $sample->sum(function (Cluster $cluster) use ($mean) {
            return pow($cluster->markers->count() - $mean, 2);
        }) / $sample->count();
    }
}
