<?php

namespace EmilKlindt\MarkerClusterer\Exceptions;

use Exception;
use EmilKlindt\MarkerClusterer\Models\Config;

class InvalidAlgorithmConfig extends Exception
{
    public function __construct(string $message, Config $config)
    {
        $json = json_encode($config);
        parent::__construct("$message: $json");
    }
}
