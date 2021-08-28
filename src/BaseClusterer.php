<?php

namespace EmilKlindt\MarkerClusterer;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;

abstract class BaseClusterer
{
    /**
     * The configuration of the clusterer
     */
    protected Config $config;

    /**
     * Collection of markers not yet clustered
     */
    protected Collection $markers;

    /**
     * Collection of clusters with markers
     */
    protected Collection $clusters;

    /**
     * Create a new instance of the clusterer
     *
     * @throws InvalidAlgorithmConfig
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?: new Config();

        $this->mergeDefaultConfig();
        $this->makeConfigImmutable();

        if (!$this->validateConfig()) {
            throw new InvalidAlgorithmConfig();
        }

        $this->setup();
    }

    /**
     * Merge the provided config with default values
     */
    private function mergeDefaultConfig(): void
    {
        $map = [
            'iterations' => config('clusterer.default_maximum_iterations'),
            'samples' => config('clusterer.default_maximum_samples'),
        ];

        foreach ($map as $key => $value) {
            if (is_null($this->config->$key)) {
                $this->config->$key = $value;
            }
        }
    }

    /**
     * Make the config immutable such that it cannot be changed
     * during or after clustering
     */
    private function makeConfigImmutable(): void
    {
        $this->config = $this->config->immutable($this->config->toArray());
    }

    /**
     * Get the config used for clustering
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Retrieve a deep-cloned collection of the clusters
     */
    protected function getClonedClusters(): Collection
    {
        $clusters = clone $this->clusters;

        return $clusters
            ->each(function (Cluster $cluster) {
                $cluster->centroid = clone $cluster->centroid;
            });
    }

    /**
     * Perform necessary setup of the algorithm
     */
    protected function setup(): void {}

    /**
     * Validate that the config is sufficient for the algorithm
     */
    abstract function validateConfig(): bool;

    /**
     * Add a new marker to the clusterer
     */
    abstract function addMarker(Clusterable $marker): void;

    /**
     * Get the clusters derived from the added markers
     */
    abstract function getClusters(): Collection;
}
