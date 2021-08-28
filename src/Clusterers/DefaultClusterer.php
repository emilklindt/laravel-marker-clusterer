<?php

namespace EmilKlindt\MarkerClusterer\Clusterers;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClusterer\BaseClusterer;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;

class DefaultClusterer extends BaseClusterer
{
    /**
     * Clusterer specified in marker clusterer config
     */
    private BaseClusterer $clusterer;

    /**
     * Create a new instance of the clusterer
     *
     * @throws AlgorithmConfigInvalid
     */
    public function __construct(?Config $config = null)
    {
        parent::__construct($config);

        $this->clusterer = new ${config('marker-clustering.default_clusterer')}();
    }

    /**
     * Perform necessary setup of the algorithm
     */
    protected function setup(): void
    {
        $this->clusterer->setup();
    }

    /**
     * Validate that the config is sufficient for the algorithm
     */
    public function validateConfig(): bool {
        return $this->clusterer->validateConfig();
    }

    /**
     * Add a new marker to the clusterer
     */
    public function addMarker(Clusterable $marker): void {
        $this->clusterer->addMarker($marker);
    }

    /**
     * Get the clusters derived from the added markers
     */
    public function getClusters(): Collection {
        return $this->clusterer->getClusters();
    }
}
