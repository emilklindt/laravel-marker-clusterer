<?php

namespace EmilKlindt\MarkerClusterer\Models;

use Spatie\DataTransferObject\DataTransferObject;

class Config extends DataTransferObject
{
    /**
     * Max number of clusters, or zero for no limit
     */
    public ?int $k;

    /**
     * Maximum number of clustering iterations
     *
     * @see config/marker-clusterer.php
     */
    public ?int $iterations;

    /**
     * Maximum movement of a cluster between iterations,
     * for it to count as convergence
     *
     * @see config/marker-clusterer.php
     */
    public ?int $convergenceMaximum;

    /**
     * Maximum number of clustering samples
     *
     * @see config/marker-clusterer.php
     */
    public ?int $samples;

    /**
     * Formula used for calculating distance between points
     *
     * @see src/Enums/DistanceFormula.php
     * @see config/marker-clusterer.php
     */
    public ?string $distanceFormula;
}
