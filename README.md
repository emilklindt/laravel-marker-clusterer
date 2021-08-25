# Server-side marker clustering in Laravel

[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/emilklindt/laravel-marker-clusterer.svg?style=flat-square)](https://packagist.org/packages/emilklindt/laravel-marker-clusterer)
![MIT License](https://img.shields.io/packagist/l/emilklindt/laravel-marker-clusterer.svg?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/emilklindt/laravel-marker-clusterer.svg?style=flat-square)

The `emilklindt/laravel-marker-clusterer` package allows you to cluster markers, before sending them to the client side.

This has the benefits of being less computational intensive for the client (with larger number of markers), and lowers overall network consumption. The cost is *of course* server computation, which might be worth it depending on the use case.

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

## Usage

Currently only K-means clustering is implemented, which is therefore also the default.

In order to easily manage which clustering algorithm is used throughout your applicaiton, you are encouraged to use the `DefaultClusterer` class. This uses the `default_clusterer` value in the `marker-clusterer` config.

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

Now the list of cars can be clustered like so:

```php
$cars = Car::get();
$clusters = DefaultClusterer::cluster($cars);
```

### K-means clustering

The K-means clustering algorithm requires a few configuration attributes. The only one that does not have a default value, is the variable `k`. This determines the maximum number of clusters.

```php
$config = new Config([
    'k' => 5
]);

$clusters = KmeansClusterer::cluster($cars, $config);
```

Other than the `k` value, the algorithm also uses configuration values to specify limitations and more. These are:

* `samples`: Number of times new random clusters are initialized from points set. The best sample is the one with lowest variance. Higher value is more optimal, lower is more performant. *(default: 10)*

* `iterations`: After random clusters have been initialized, this specifies the maximum number of times to re-calculate the center of the clusters and assign points to nearest clusters, for each sample. Higher value is more optimal, lower is more performant. *(default: 10)*

* `distanceFormula`: The formula used for calculating distance between points. The distance is used for calculating nearest cluster to point. Valid values are specified in the `DistanceFormula` enum. *(default: haversine)*

In order to change the default values, see the [publishing config file](#Publish-config-file) section.

## Testing

``` bash
composer test
```

## Credits

Thanks to [Spatie](https://github.com/spatie) for providing inspiration and useful resources for package development.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
