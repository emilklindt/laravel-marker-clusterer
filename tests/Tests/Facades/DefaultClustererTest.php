<?php

namespace EmilKlindt\MarkerClusterer\Tests\Facades;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Facades\DefaultClusterer;
use EmilKlindt\MarkerClusterer\Clusterers\KMeansClusterer;
use EmilKlindt\MarkerClusterer\MarkerClustererServiceProvider;
use EmilKlindt\MarkerClusterer\Clusterers\DensityBasedSpatialClusterer;
use EmilKlindt\MarkerClusterer\Test\Factories\ConfigFactory;

class DefaultClustererTest extends TestCase
{
    const CONFIG_KEY = 'marker-clusterer.default_clusterer';

    public function setUp(): void
    {
        parent::setUp();

        $this->app->register(new MarkerClustererServiceProvider($this->app));
    }

    public function clustererProvider()
    {
        return [
            ['density-based-spatial-clusterer', DensityBasedSpatialClusterer::class],
            ['k-means-clusterer', KMeansClusterer::class]
        ];
    }

    /**
     * @test
     * @dataProvider clustererProvider
     **/
    public function it_resolves_to_default_clusterer(string $defaultClusterer, string $className)
    {
        config()->set(self::CONFIG_KEY, $defaultClusterer);

        $this->assertInstanceOf($className, DefaultClusterer::getFacadeRoot());
    }

    /**
     * @test
     * @dataProvider clustererProvider
     **/
    public function it_proxies_methods(string $defaultClusterer)
    {
        config()->set(self::CONFIG_KEY, $defaultClusterer);

        $markers = new Collection();
        $config = $this->configFactory->make();

        $this->assertInstanceOf(Collection::class, DefaultClusterer::cluster($markers, $config));
    }
}
