<?php

namespace EmilKlindt\MarkerClusterer\Test\Clusterers;

use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Test\Stubs\MarkerStub;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;
use EmilKlindt\MarkerClusterer\Clusterers\DensityBasedSpatialClusterer;

class DensityBasedSpatialClustererTest extends TestCase
{
    private function getValidConfig()
    {
        return new Config([
            'epsilon' => 5.0,
            'minSamples' => 3,
            'includeNoise' => false,
            'distanceFormula' => DistanceFormula::MANHATTAN,
        ]);
    }

    private function getRandomMarker()
    {
        return new MarkerStub(
            mt_rand(-90, 90),
            mt_rand(-180, 180)
        );
    }

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
        $config = $this->getValidConfig();

        new DensityBasedSpatialClusterer($config);

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_successfully_includes_noise()
    {
        $config = $this->getValidConfig();
        $config->includeNoise = true;

        $clusterer = new DensityBasedSpatialClusterer($config);
        $marker = $this->getRandomMarker();
        $clusterer->addMarker($marker);

        $clusters = $clusterer->getClusters();

        $this->assertCount(1, $clusters);
        $this->assertEquals($marker, $clusters->first()->markers->first());
    }

    /** @test */
    public function it_successfully_excludes_noise()
    {
        $config = $this->getValidConfig();
        $config->includeNoise = false;

        $clusterer = new DensityBasedSpatialClusterer($config);
        $marker = $this->getRandomMarker();
        $clusterer->addMarker($marker);

        $clusters = $clusterer->getClusters();

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
        $clusterer->addMarker(new MarkerStub(55.373772, 10.411701));
        $clusterer->addMarker(new MarkerStub(55.375606, 10.409265));
        $clusterer->addMarker(new MarkerStub(55.377179, 10.413943));

        // city 2
        $clusterer->addMarker(new MarkerStub(55.784164, 12.524051));
        $clusterer->addMarker(new MarkerStub(55.785509, 12.522738));

        $clusters = $clusterer->getClusters();

        $this->assertCount(2, $clusters);

        $this->assertEquals(5, $clusters->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }));

        $this->assertContainsOnlyInstancesOf(MarkerStub::class, $clusters
            ->map(function (Cluster $cluster) {
                return $cluster->markers;
            })
            ->flatten());
    }
}
