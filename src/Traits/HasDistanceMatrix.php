<?php

namespace EmilKlindt\MarkerClusterer\Traits;

use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use Illuminate\Support\Collection;
use League\Geotools\Coordinate\Coordinate;

trait HasDistanceMatrix
{
    /**
     * Matrix (2-dimensional) of distances from marker to marker
     */
    private Collection $distanceMatrix;

    /**
     * Calculate and set values for distance matrix
     */
    protected function setDistanceMatrix(): void
    {
        $this->distanceMatrix = new Collection();

        // calculate distance matrix
        $this->markers
            ->each(function (Clusterable $marker, int $y) {
                $this->distanceMatrix->put($y, new Collection());

                for ($x = 0; $x <= $y; $x++) {
                    $this->distanceMatrix->get($y)->put(
                        $x,
                        $this->distanceCalculator
                            ->measure(
                                $marker->getClusterableCoordinate(),
                                $this->markers->get($x)->getClusterableCoordinate())
                    );
                }
            });

        // diagonally mirror matrix, for faster read access
        $this->markers
            ->each(function (Clusterable $marker, int $y) {
                for ($x = $y + 1; $x < $this->markers->count(); $x++) {
                    $this->distanceMatrix->get($y)->put(
                        $x,
                        $this->distanceMatrix
                            ->get($x)
                            ->get($y)
                    );
                }
            });
    }

    /**
     * Get all distances from a certain index
     */
    protected function getDistancesFromIndex(int $index): Collection
    {
        return $this->distanceMatrix->get($index);
    }
}
