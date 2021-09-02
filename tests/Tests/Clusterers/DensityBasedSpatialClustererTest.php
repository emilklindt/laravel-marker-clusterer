<?php

namespace EmilKlindt\MarkerClusterer\Tests\Clusterers;

use Illuminate\Support\Collection;
use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;
use EmilKlindt\MarkerClusterer\Clusterers\DensityBasedSpatialClusterer;

class DensityBasedSpatialClustererTest extends TestCase
{
    /** @test */
    public function it_fails_with_invalid_config()
    {
        $this->expectException(InvalidAlgorithmConfig::class);

        $config = new Config();

        new DensityBasedSpatialClusterer($config);
    }

    /** @test */
    public function it_validates_valid_config()
    {
        $config = $this->configFactory->make();

        new DensityBasedSpatialClusterer($config);

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_returns_clusters_and_markers_collection()
    {
        $config = $this->configFactory->make([
            'minSamples' => 2,
            'includeNoise' => true,
        ]);

        $clusterer = new DensityBasedSpatialClusterer($config);

        $clusters = $clusterer
            ->addMarker($this->makeMarker())
            ->addMarker($this->makeMarker())
            ->getClusters();

        $this->assertInstanceOf(Collection::class, $clusters);
        $this->assertContainsOnlyInstancesOf(Cluster::class, $clusters);

        $this->assertContainsOnlyInstancesOf(Clusterable::class, $clusters
            ->map(function (Cluster $cluster) {
                return $cluster->markers;
            })
            ->flatten());
    }

    /** @test */
    public function it_successfully_includes_noise()
    {
        $config = $this->configFactory->make([
            'minSamples' => 2,
            'includeNoise' => true,
        ]);

        $clusterer = new DensityBasedSpatialClusterer($config);

        $clusters = $clusterer
            ->addMarker($marker = $this->makeMarker())
            ->getClusters();

        $this->assertCount(1, $clusters);
        $this->assertEquals($marker, $clusters->first()->markers->first());
    }

    /** @test */
    public function it_successfully_excludes_noise()
    {
        $config = $this->configFactory->make([
            'minSamples' => 2,
            'includeNoise' => false,
        ]);

        $clusterer = new DensityBasedSpatialClusterer($config);

        $clusters = $clusterer
            ->addMarker($this->makeMarker())
            ->getClusters();

        $this->assertCount(0, $clusters);
    }

    /** @test */
    public function it_successfully_clusters_multiple_markers()
    {
        $config = new Config([
            'epsilon' => 500.0,
            'minSamples' => 2,
            'includeNoise' => true,
            'distanceFormula' => DistanceFormula::HAVERSINE,
        ]);

        $clusterer = new DensityBasedSpatialClusterer($config);

        // city 1
        $clusterer->addMarker($this->makeMarker(['lat' => 55.373772, 'lng' => 10.411701]));
        $clusterer->addMarker($this->makeMarker(['lat' => 55.375606, 'lng' => 10.409265]));
        $clusterer->addMarker($this->makeMarker(['lat' => 55.377179, 'lng' => 10.413943]));

        // city 2
        $clusterer->addMarker($this->makeMarker(['lat' => 55.784164, 'lng' => 12.524051]));
        $clusterer->addMarker($this->makeMarker(['lat' => 55.785509, 'lng' => 12.522738]));

        $clusters = $clusterer->getClusters();

        $this->assertCount(2, $clusters);
        $this->assertEquals(5, $clusters->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }));
    }
}
