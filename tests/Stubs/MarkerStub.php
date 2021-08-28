<?php

namespace EmilKlindt\MarkerClusterer\Tests\Stubs;

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
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the latitude/longitude coordinate of the point
     */
    public function getClusterableCoordinate(): Coordinate
    {
        return new Coordinate([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }
}
