<?php

namespace EmilKlindt\MarkerClusterer\Test\Clusterers;

use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Test\Stubs\MarkerStub;
use EmilKlindt\MarkerClusterer\Clusterers\KMeansClusterer;
use EmilKlindt\MarkerClusterer\Exceptions\InvalidAlgorithmConfig;

class KMeansClustererTest extends TestCase
{
    private function getValidConfig()
    {
        return new Config([
            'k' => 3,
            'iterations' => 10,
            'samples' => 10,
            'distanceFormula' => DistanceFormula::HAVERSINE,
            'convergenceMaximum' => 1000,
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

        new KMeansClusterer($config);
    }

    /** @test */
    public function it_validates_valid_config()
    {
        $config = $this->getValidConfig();

        new KMeansClusterer($config);

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_successfully_adds_markers()
    {
        $clusterer = new KMeansClusterer($this->getValidConfig());

        $marker = new MarkerStub($lat = 55, $lng = 9);

        $clusterer->addMarker($marker);

        $clusters = $clusterer->getClusters();

        $this->assertCount(1, $clusters);
        $this->assertEquals($marker, $clusters->first()->markers->first());
    }

    /** @test */
    public function it_successfully_clusters_larger_than_k_markers()
    {
        $config = $this->getValidConfig();
        $config->k = 3;

        $clusterer = new KMeansClusterer($config);

        for ($i = 0; $i < $n = 50; $i++) {
            $clusterer->addMarker($this->getRandomMarker());
        }

        $clusters = $clusterer->getClusters();

        $this->assertCount($config->k, $clusters);
        $this->assertEquals($n, $clusters->sum(function (Cluster $cluster) {
            return $cluster->markers->count();
        }));
    }
}
