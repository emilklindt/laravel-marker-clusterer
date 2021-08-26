<?php

namespace EmilKlindt\MarkerClustering\Tests\Stubs;

use EmilKlindt\MarkerClustering\Models\Coordinate;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;

class PointStub implements Clusterable
{
    /**
     * The latitude of the point.
     *
     * @var float
     */
    private float $latitude;

    /**
     * The longitude of the point.
     *
     * @var float
     */
    private float $longitude;

    /**
     * Create a new point instance.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the latitude/longitude coordinate of the point.
     *
     * @return Coordinate
     */
    public function getClusterableCoordinate(): Coordinate
    {
        return new Coordinate([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }
}
