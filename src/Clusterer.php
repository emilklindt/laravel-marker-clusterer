<?php

namespace EmilKlindt\MarkerClustering;

use ArrayAccess;
use Illuminate\Support\Collection;
use EmilKlindt\MarkerClustering\Models\Config;
use EmilKlindt\MarkerClustering\Models\Cluster;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;
use EmilKlindt\MarkerClustering\Algorithms\KMeansClustering;
use EmilKlindt\MarkerClustering\Exceptions\ConfigNotSupportedByAlgorithm;
use EmilKlindt\MarkerClustering\Traits\ConfigurableAlgorithm;
use EmilKlindt\MarkerClustering\Interfaces\ClusteringAlgorithm;
use EmilKlindt\MarkerClustering\Exceptions\InvalidAlgorithmConfig;
use EmilKlindt\MarkerClustering\Exceptions\IllegalClusterNumberChange;

class Clusterer extends Collection
{
    /**
     * Points to cluster
     */
    private Collection $points;

    /**
     * Clusters derived from points
     */
    private Collection $clusters;

    /**
     * The algorithm implementation used for clustering
     */
    private ClusteringAlgorithm $algorithm;

    /**
     * Create a new instance of the clusterer
     */
    public function __construct(ClusteringAlgorithm $algorithm = null, Config $config = null)
    {
        $this->points = new Collection();
        $this->clusters = new Collection();
        $this->algorithm = $algorithm ?: new KMeansClustering();

        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    private function setConfig(Config $config)
    {
        $uses = class_uses($this->algorithm);

        if ($uses === false || !in_array(ConfigurableAlgorithm::class, $uses)) {
            throw new ConfigNotSupportedByAlgorithm();
        }

        $this->algorithm->setConfig($config);

        if (!$this->algorithm->validateConfig()) {
            throw new InvalidAlgorithmConfig("The provided clustering config is invalid for algorithm {get_class($this->algorithm)}");
        }
    }

    /**
     * Add new point to the clusterer
     */
    public function addPoint(Clusterable $point): void
    {
        $this->algorithm->addPoint($this->points, $this->clusters, $point);
    }

    /**
     * Get the clusters aggregated from added points
     */
    public function getClusters(): Collection
    {
        return $this->algorithm->getClusters($this->points, $this->clusters);
    }
}
