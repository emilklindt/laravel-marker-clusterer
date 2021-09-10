<?php

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

    'default_clusterer' => 'density-based-spatial-clusterer',

    /*
    |--------------------------------------------------------------------------
    | Default Distance Formula
    |--------------------------------------------------------------------------
    |
    | The default formula for calculating distance from one coordinate to
    | another. Possible values are constants of the DistanceFormula enum.
    |
    */

    'default_distance_formula' => \EmilKlindt\MarkerClusterer\Enums\DistanceFormula::MANHATTAN,

    /*
    |--------------------------------------------------------------------------
    | K-means Clustering
    |--------------------------------------------------------------------------
    |
    | K-means algorithm identifies k number of centroids, and then allocates
    | every data point to the nearest cluster.
    |
    */

    'k_means' => [

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

        'default_convergence_maximum' => 100.0,

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Density Based Spatial Clusterer (DBSCAN)
    |--------------------------------------------------------------------------
    |
    | Finds core samples of high density and expands clusters from them.
    |
    */

    'dbscan' => [

        /*
        |--------------------------------------------------------------------------
        | Default Include Noise
        |--------------------------------------------------------------------------
        |
        | Whether to include markers not meeting the threshold of minSamples.
        | If true, markers not within epsilon distance of at least minSamples,
        | will be included anyways, in a solo cluster for that given point.
        */

        'default_include_noise' => true,

        /*
        |--------------------------------------------------------------------------
        | Default Use Geohash Neighboring
        |--------------------------------------------------------------------------
        |
        | When response time is critical and precision is not, it may sometimes
        | be beneficial to use geohashing for neighbor searching only. A geohash
        | is calculated for every marker when added to the clusterer. This is
        | used to limit the scope of distance calculations to only points that
        | fall within neighboring geohashes.
        |
        | Enabling this setting will remove the last step, which is calculating
        | exact distance to each marker in the neighboring geohashes, and then
        | comparing it against the epsilon value.
        |
        | The geohash precision is based on the epsilon value, so by specifycing
        | a larger epsilon value, more markers will be considered neighbors etc.
        */

        'default_use_geohash_neighboring' => false,
    ]
];
