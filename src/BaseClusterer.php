<?php

namespace EmilKlindt\MarkerClusterer;

use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;
use EmilKlindt\MarkerClusterer\Support\DistanceCalculator;

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
     * Determine distance between markers
     */
    protected DistanceCalculator $distanceCalculator;

    /**
     * Create a new instance of the clusterer
     *
     * @throws InvalidAlgorithmConfig
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?: new Config();

        $this->markers = new Collection();
        $this->clusters = new Collection();

        $this->mergeDefaultConfig();

        if (!$this->validateConfig()) {
            throw new InvalidAlgorithmConfig('Config invalid for algorithm', $this->config);
        }

        $this->setup();

        $this->distanceCalculator = new DistanceCalculator($this->config->distanceFormula);
    }

    /**
     * Merge the provided config with default values
     */
    private function mergeDefaultConfig(): void
    {
        $map = [
            'samples' => config('marker-clusterer.default_maximum_samples'),
            'iterations' => config('marker-clusterer.default_maximum_iterations'),
            'distanceFormula' => config('marker-clusterer.default_distance_formula'),
            'convergenceMaximum' => config('marker-clusterer.default_convergence_maximum'),
        ];

        foreach ($map as $key => $value) {
            if (is_null($this->config->$key)) {
                $this->config->$key = $value;
            }
        }
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
    protected function setup(): void
    {

    }

    /**
     * Validate that the config is sufficient for the algorithm
     */
    protected function validateConfig(): bool
    {
        return true;
    }

    /**
     * Add a new marker to the clusterer
     */
    abstract function addMarker(Clusterable $marker): void;

    /**
     * Get the clusters derived from the added markers
     */
    abstract function getClusters(): Collection;

    /**
     * Shorthand method for clustering a group of markers
     */
    static function cluster(Collection $markers, ?Config $config = null): Collection
    {
        $clusterer = new static($config);

        $markers->each(function (Clusterable $marker) use ($clusterer) {
            $clusterer->addMarker($marker);
        });

        return $clusterer->getClusters();
    }
}
