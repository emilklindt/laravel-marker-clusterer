<?php

namespace EmilKlindt\MarkerClustering\Traits;

use EmilKlindt\MarkerClustering\Models\Config;

trait ConfigurableAlgorithm
{
    /**
     * The config for the algorithm
     */
    private Config $config;

    /**
     * Set the config for the algorithm
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * Validate that the config has sufficient and valid parameters
     */
    public function validateConfig(): bool
    {
        return true;
    }
}
