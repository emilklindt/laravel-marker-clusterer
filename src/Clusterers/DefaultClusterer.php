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
    public function __construct(?Config $config = null, ?BaseClusterer $clusterer = null)
    {
        $default = config('marker-clusterer.default_clusterer');

        $this->clusterer = $clusterer ?: new $default($config);
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
