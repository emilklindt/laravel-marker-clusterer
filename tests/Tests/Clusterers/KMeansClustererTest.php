<?php

namespace EmilKlindt\MarkerClusterer\Tests\Clusterers;

use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Clusterers\KMeansClusterer;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;

class KMeansClustererTest extends TestCase
{
    /** @test */
    public function it_fails_with_invalid_config()
    {
        $this->expectException(InvalidAlgorithmConfig::class);

        $config = new Config();

        new KMeansClusterer($config);
    }

    /** @test */
    public function it_validates_valid_config()
    {
        $config = $this->configFactory->make();

        new KMeansClusterer($config);

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_successfully_adds_markers()
    {
        $clusterer = new KMeansClusterer($this->configFactory->make());

        $clusters = $clusterer
            ->addMarker($marker = $this->makeMarker(['lat' => 55, 'lng' => 9]))
            ->getClusters();

        $this->assertCount(1, $clusters);
        $this->assertEquals($marker, $clusters->first()->markers->first());
    }

    /** @test */
    public function it_successfully_clusters_larger_than_k_markers()
    {
        $config = $this->configFactory->make();
        $config->k = 3;

        $clusterer = new KMeansClusterer($config);

        for ($i = 0; $i < $n = 50; $i++) {
            $clusterer->addMarker($this->makeMarker());
        }

        $clusters = $clusterer->getClusters();

        $this->assertCount($config->k, $clusters);
        $this->assertEquals($n, $clusters->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }));
    }
}
