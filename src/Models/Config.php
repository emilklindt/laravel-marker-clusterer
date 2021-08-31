<?php

namespace EmilKlindt\MarkerClusterer\Models;

use Spatie\DataTransferObject\DataTransferObject;

class Config extends DataTransferObject
{
    /**
     * Max number of clusters, or zero for no limit.
     */
    public ?int $k;

    /**
     * Maximum number of clustering iterations.
     *
     * @see config/marker-clusterer.php, k_means
     */
    public ?int $iterations;

    /**
     * Maximum movement of a cluster between iterations,
     * for it to count as convergence.
     *
     * @see config/marker-clusterer.php, k_means
     */
    public ?float $convergenceMaximum;

    /**
     * Maximum number of clustering samples.
     *
     * @see config/marker-clusterer.php, k_means
     */
    public ?int $samples;

    /**
     * Formula used for calculating distance between points.
     *
     * @see src/Enums/DistanceFormula.php
     * @see config/marker-clusterer.php, k_means
     */
    public ?string $distanceFormula;

    /**
     * The maximum distance between two samples for one to
     * be considered as in the neighborhood of the other.
     *
     * @link https://scikit-learn.org/stable/modules/generated/sklearn.cluster.DBSCAN.html
     */
    public ?float $epsilon;

    /**
     * The number of samples (or total weight) in a neighbor-
     * hood for a point to be considered as a core point. This
     * includes the point itself.
     *
     * @link https://scikit-learn.org/stable/modules/generated/sklearn.cluster.DBSCAN.html
     */
    public ?int $minSamples;

    /**
     * Whether to include markers not meeting the threshold of
     * minSamples. If true, markers not within epsilon distance
     * of at least minSamples, will be included anyways, in a
     * solo cluster for that given point.
     *
     * @var boolean|null
     */
    public ?bool $includeNoise;
}
