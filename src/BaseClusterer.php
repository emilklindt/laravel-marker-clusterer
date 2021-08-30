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
     * The configuration of the clusterer.
     */
    protected Config $config;

    /**
     * Collection of markers not yet clustered.
     */
    protected Collection $markers;

    /**
     * Collection of clusters with markers.
     */
    protected Collection $clusters;

    /**
     * Determine distance between markers.
     */
    protected DistanceCalculator $distanceCalculator;

    /**
     * Create a new instance of the clusterer.
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
     * If not set already, set specified config value for key
     */
    protected function setDefaultConfig(string $key, ?mixed $value = null): bool
    {
        if (is_null($this->config->$key) && !is_null($value)) {
            $this->config->$key = $value;
            return true;
        }

        return false;
    }

    /**
     * Retrieve a deep-cloned collection of the clusters.
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
     * Merge the provided config with default values.
     */
    protected abstract function mergeDefaultConfig(): void;

    /**
     * Perform necessary setup of the algorithm.
     */
    protected abstract function setup(): void;

    /**
     * Validate that the config is sufficient for the algorithm.
     */
    protected abstract function validateConfig(): bool;

    /**
     * Add a new marker to the clusterer.
     */
    abstract function addMarker(Clusterable $marker): void;

    /**
     * Get the clusters derived from the added markers.
     */
    abstract function getClusters(): Collection;

    /**
     * Calculate the mean of each clusters as new centroid.
     */
    protected function updateClusterCentroids(): void
    {
        $this->clusters
            ->each(function (Cluster $cluster) {
                $coordinates = $cluster->markers
                    ->map(function (Clusterable $marker) {
                        return $marker->getClusterableCoordinate();
                    });

                $cluster->centroid = new Coordinate([
                    $coordinates->avg(function (Coordinate $coordinate) {
                        return $coordinate->getLatitude();
                    }),
                    $coordinates->avg(function (Coordinate $coordinate) {
                        return $coordinate->getLongitude();
                    })
                ]);
            });
    }

    /**
     * Shorthand method for clustering a group of markers.
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
