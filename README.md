# Server-side marker clustering in Laravel

[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/emilklindt/laravel-marker-clusterer.svg?style=flat-square)](https://packagist.org/packages/emilklindt/laravel-marker-clusterer)
![MIT License](https://img.shields.io/packagist/l/emilklindt/laravel-marker-clusterer.svg?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/emilklindt/laravel-marker-clusterer.svg?style=flat-square)

The `emilklindt/laravel-marker-clusterer` package allows you to cluster markers, before sending them to the client side.

This has the benefits of being less computational intensive for the client (with larger number of markers), and lowers overall network consumption. The cost is *of course* server computation, which might be worth it depending on the use case.

<br/>
<details open="open">
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#installation">Installation</a>
      <ul>
        <li><a href="#publish-config-file">Publish config file</a></li>
      </ul>
    </li>
    <li>
      <a href="#usage">Usage</a>
      <ul>
        <li><a href="#adding-your-markers">Adding markers</a></li>
        <li><a href="#clustering-your-markers">Clustering markers</a></li>
      </ul>
    </li>
    <li>
      <a href="#clusterers">Clusterers</a>
      <ul>
        <li><a href="#k-means-clustering">K-means Clusterer</a></li>
        <li><a href="#density-based-spatial-clustering">Density-Based Spatial Clusterer</a></li>
      </ul>
    </li>
    <li><a href="#testing">Testing</a></li>
    <li><a href="#credits">Credits</a></li>
    <li><a href="#license">License</a></li>
  </ol>
</details>

## Installation

You can install the package via composer:

```bash
composer require emilklindt/laravel-marker-clusterer
```

The package will automatically register itself.

### Publish config file

You can optionally publish the config file with:

```bash
php artisan vendor:publish --provider="EmilKlindt\MarkerClusterer\MarkerClustererServiceProvider" --tag="config"
```

This is the contents of the file that will be published at `config/marker-clusterer.php`:

```php
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

        'default_distance_formula' => \EmilKlindt\MarkerClusterer\Enums\DistanceFormula::MANHATTAN,
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
    ]
];
```

## Usage

In order to manage which clustering algorithm is used throughout your applicaiton, you are encouraged to use the `DefaultClusterer` class. This uses the `default_clusterer` value in the `marker-clusterer` config, which can be easily changed by [publishing the configuration](#Publish-config-file).

```php
$clusters = DefaultClusterer::cluster($markers, $config);
```

### Adding your markers

The clusterer takes a collection of markers. However, these markers have to implement the `Clusterable` interface, which has a single method for retrieving the marker coordinate.

An example implementation in your Eloquent model may look like this:

```php
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;

class Car extends Model implements Clusterable
{
    public function getClusterableCoordinate(): Coordinate
    {
        return new Coordinate([
            $this->lat,
            $this->lng
        ]);
    }
}
```

### Clustering your markers

With the interface implemented, a collection of cars may be clustered like so:

```php
$cars = Car::get();

$config = new Config([
    'epsilon' => 10.5,
    'minSamples' => 2
]);

$clusters = DefaultClusterer::cluster($cars, $config);
```

See the [clusters](#clusterers) section below for the different clustering methods and their parameters.

## Clusterers

Contributions or feature requests for other clusterers are welcomed. Feel free to create a pull request or an issue labeled *feature-request*.

![DBSCAN vs. K-means](./.docs/assets/clusterers.png)
*Credit: [NSHipster/DBSCAN](https://github.com/NSHipster/DBSCAN) repository*

### K-means clustering

Perhaps the most well known algorithm in clustering, [*k*-means clustering](https://en.wikipedia.org/wiki/K-means_clustering) will partition *n* elements into *k* clusters.

As shown illustratively above, K-means clusters markers into the cluster with the nearest mean, meaning the least distance from marker to the center of the cluster. K-means requires a *k* value, which is the number of clusters desired. There are [many different ways](https://en.wikipedia.org/wiki/Determining_the_number_of_clusters_in_a_data_set) of choosing this value, depending on your dataset, however this is not yet adressed in this repository.

```php
$config = new Config([
    'k' => 3
]);

$clusters = KMeansClusterer::cluster($cars, $config);
```

Configuration parameters applicable to the `KMeansClusterer`:

* `k`, *integer* (required)  
  The desired number of clusters, as well as the initial number of *centroids* (cluster points) to initialize randomly. If *k* is larger than the number of markers, all markers will be clustered individually.

* `iterations`, *integer* (optional)  
  The maximum number of iterations to recalculate the centroid (mean position of cluster points), and assign markers to the nearest cluster. After clusters have been randomly initialized, markers are assigned nearest cluster, after which the cluster centroid is recalculated, and the process is repeated.

* `convergenceMaximum`, *float* (optional)  
  The maximum distance between a cluster's centroids in two consecutive iterations, in order to declare convergence, which along with `iterations` is a stopping criterion for generating a sample of clusters. This value may need tuning when changing the `distanceFormula`, as each distance formula has varied levels of accuracy.

* `samples`, *int* (optional)  
  Number of times to initialize random centroids for the clusters, and perform `iterations` number of optimization iterations. After `samples` number of clustered samples has been found, the cluster sample with the lowest variance is choosen ([illustration](./.docs/assets/variance.png), by [Arif R, Medium](https://medium.com/data-folks-indonesia/step-by-step-to-understanding-k-means-clustering-and-implementation-with-sklearn-b55803f519d6)). A higher value is more likely to yield an optimal result, at the cost of performance.

* `distanceFormula`, *string* (optional)  
  The formula used for calculating distance between points. Distances are used to determine nearest cluster to marker. Valid values are constants of the DistanceFormula enum. Haversine distance is preferable, if precision is important to the clustering task.

All properties marked optional has default values, specified in the configuration. See the [publishing config file](#Publish-config-file) section above to manipulate these.

### Density-Based Spatial clustering

Algorithm used is [Density-Based Spatial Clustering of Applications with Noise](https://en.wikipedia.org/wiki/DBSCAN) (DBSCAN).

This clustering method overcomes a common issue with K-means clustering, namely the need to specify the number of clusters. This parameter may be dependent on the data set – more specifically, the density of the dataset. The DBSCAN algorithm takes an *epsilon* (maximum distance between two points to be grouped together) and *minSamples* (minimum amount of points to be grouped, to form a cluster).

The "Application with Noise" portion means, that the clustering method is able to handle *noise*. Meaning, points not immediately related to a cluster, may be either discarded or returned as single points, depending on the *includeNoise* configuration.

```php
$config = new Config([
    'epsilon' => 10.5,
    'minSamples' => 5
]);

$clusters = DensityBasedSpatialClusterer::cluster($cars, $config);
```

Configuration parameters applicable to the `DensityBasedSpatialClusterer`:

* `epsilon`, *float* (required)  
  The maximum distance between two markers for one to be considered as in the neighborhood of the other. This is not to be considered as the maximum cluster size, as by doing multiple steps the cluster may become much larger than `epsilon`.

* `minSamples`, *integer* (required)  
  The number of markers in a neighborhood for a point to be considered as a core point to a new cluster. If a point *p* has over `minSamples` neighbors within `epsilon` distance, it is eligible to be core point for a new cluster. The algorithm creates a new cluster from this point, and adds all points within `epsilon` distance repeatedly, untill no more points are within reach.

* `includeNoise`, *boolean* (optional)  
  Whether to include markers not meeting the threshold of `minSamples`. If true, all markers not within `epsilon` distance of a cluster, will be included as individual clusters.

* `distanceFormula`, *string* (optional)  
  The formula used for calculating distance between points. Distances are used to determine nearest cluster to marker. Valid values are constants of the DistanceFormula enum. Haversine distance is preferable, if precision is important to the clustering task.

All properties marked optional has default values, specified in the configuration. See the [publishing config file](#Publish-config-file) section above to manipulate these.

## Testing

``` bash
composer test
```

## Credits

Thanks to [Spatie](https://github.com/spatie) for providing inspiration and useful resources for package development.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
