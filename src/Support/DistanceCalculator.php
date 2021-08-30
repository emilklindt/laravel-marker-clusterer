<?php

namespace EmilKlindt\MarkerClusterer\Support;

use InvalidArgumentException;
use League\Geotools\Geotools;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use League\Geotools\Distance\Distance;

class DistanceCalculator
{
    /**
     * The formula used for calculating distance
     */
    private string $formula;

    /**
     * Geotools instance used for euclidean distance
     */
    private Geotools $geotools;

    /**
     * Geotools distance instance used for euclidean distance
     */
    private Distance $distance;

    /**
     * Create a new instance of the distance calculator
     *
     * @param DistanceFormula $formula
     */
    public function __construct(string $formula)
    {
        if (!in_array($formula, DistanceFormula::getConstants())) {
            throw new InvalidArgumentException('Distance formula must be a value of DistanceFormula enum');
        }

        $this->formula = $formula;

        $this->geotools = new Geotools();
        $this->distance = $this->geotools->distance();
    }

    /**
     * Measure distance from one coordinate to another
     */
    public function measure(Coordinate $from, Coordinate $to): float
    {
        // use Geotools distance for euclidean distances
        if (method_exists($this->distance, $this->formula)) {
            $this->distance->setFrom($from);
            $this->distance->setTo($to);

            return $this->distance->{$this->formula}();
        }

        if ($this->formula === DistanceFormula::MANHATTAN) {
            return $this->manhattan($from, $to);
        }
    }

    /**
     * Measure manhattan distance from one coordinate to another, mostly
     * useful due to its performance gain over other distance formulas
     */
    private function manhattan(Coordinate $from, Coordinate $to): float
    {
        return abs($from->getLatitude() - $to->getLatitude()) * 110947.2
            + abs($from->getLongitude() - $to->getLongitude()) * 87843.36;
    }
}
