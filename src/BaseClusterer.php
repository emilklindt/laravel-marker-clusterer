<?php

namespace EmilKlindt\MarkerClusterer;

use RuntimeException;
use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Support\DistanceCalculator;
use EmilKlindt\MarkerClusterer\Exceptions\IllegalConfigChange;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;

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
     */
    public function __construct(?Config $config = null)
    {
        $this->markers = new Collection();
        $this->clusters = new Collection();

        if ($config instanceof Config) {
            $this->setConfig($config);
        }

        $this->setup();
    }

    /**
     * Set the config of the clusterer.
     *
     * @throws IllegalConfigChange
     * @throws InvalidAlgorithmConfig
     */
    public function setConfig(Config $config): self
    {
        if ($this->markers->count() !== 0 || $this->clusters->count() !== 0) {
            throw new IllegalConfigChange('Cannot change config after clustering');
        }

        $this->config = $config;
        $this->mergeDefaultConfig();

        if (!$this->validateConfig()) {
            throw new InvalidAlgorithmConfig('Config invalid for algorithm', $this->config);
        }

        $this->distanceCalculator = new DistanceCalculator($this->config->distanceFormula);

        return $this;
    }

    /**
     * If not set already, set specified config value for key.
     */
    protected function setDefaultConfigValue(string $key, $value = null): bool
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
    public static function cluster(Collection $markers, ?Config $config = null): Collection
    {
        $clusterer = new static($config);

        $markers->each(function (Clusterable $marker) use ($clusterer) {
            $clusterer->addMarker($marker);
        });

        return $clusterer->getClusters();
    }

    /**
     * Merge the provided config with default values.
     */
    abstract protected function mergeDefaultConfig(): void;

    /**
     * Perform necessary setup of the algorithm.
     */
    abstract protected function setup(): void;

    /**
     * Validate that the config is sufficient for the algorithm.
     */
    abstract protected function validateConfig(): bool;

    /**
     * Add a new marker to the clusterer.
     */
    abstract public function addMarker(Clusterable $marker): self;

    /**
     * Get the clusters derived from the added markers.
     */
    abstract public function getClusters(): Collection;

}
