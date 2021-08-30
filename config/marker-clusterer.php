<?php

use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Clusterer
    |--------------------------------------------------------------------------
    |
    | The default clustering method used when using the DefaultClusterer class
    | included in this project. This allows for easily swapping of the clusterer
    | used throughout a project, through only editing the config file.
    |
    */

    'default_clusterer' => 'KMeansClusterer',

    /*
    |--------------------------------------------------------------------------
    | Default Maximum Iterations
    |--------------------------------------------------------------------------
    |
    | The default number of maximum iterations of clustering, for example used
    | in K-means clustering, where clustering is repeated untill either reaching
    | convergence (no further changes) or the maximum number of iterations.
    |
    */

    'default_maximum_iterations' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default Maximum Convergence Distance
    |--------------------------------------------------------------------------
    |
    | The maximum distance between iterations to count a cluster as converged,
    | meaning that no further iteration is necessary. A higher value can provide
    | better performance, due to the need of doing less iterations. A lower value
    | will ensure that a cluster has actually converged.
    |
    */

    'default_convergence_maximum' => 100,

    /*
    |--------------------------------------------------------------------------
    | Default Maximum Samples
    |--------------------------------------------------------------------------
    |
    | The default number of maximum samples of clustering, for example used
    | in K-means clustering, where the specified number of samples are run
    | to achieve the lowest variance between the centroids.
    |
    | This differs from maximum iterations in that, iterations are executed
    | on the same set of initially random centroids. Each sample instantiates
    | a new set of centroids to iteratively optimize, untill maximum number
    | of iterations or convergence is reached.
    |
    */

    'default_maximum_samples' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default Distance Formula
    |--------------------------------------------------------------------------
    |
    | The default formula for calculating distance from one coordinate to
    | another. Possible values are constants of the DistanceFormula enum.
    |
    */

    'default_distance_formula' => DistanceFormula::MANHATTAN,
];
