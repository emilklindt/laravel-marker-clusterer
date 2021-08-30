<?php

namespace EmilKlindt\MarkerClusterer\Test\Stubs;

use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;

class MarkerStub implements Clusterable
{
    /**
     * The latitude of the point
     */
    private float $latitude;

    /**
     * The longitude of the point
     */
    private float $longitude;

    /**
     * Create a new point instance
     */
    public function __construct(array $coordinate)
    {
        $this->latitude = $coordinate['lat'];
        $this->longitude = $coordinate['lng'];
    }

    /**
     * Get the latitude/longitude coordinate of the point
     */
    public function getClusterableCoordinate(): Coordinate
    {
        return new Coordinate([
            $this->latitude,
            $this->longitude,
        ]);
    }
}
