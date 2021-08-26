<?php

namespace EmilKlindt\MarkerClustering\Tests;

use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Collection;
use EmilKlindt\MarkerClustering\Clusterer;
use EmilKlindt\MarkerClustering\Models\Cluster;
use EmilKlindt\MarkerClustering\Tests\TestCase;
use EmilKlindt\MarkerClustering\Tests\Stubs\PointStub;
use EmilKlindt\MarkerClustering\Interfaces\Clusterable;
use EmilKlindt\MarkerClustering\Interfaces\ClusteringAlgorithm;
use EmilKlindt\MarkerClustering\Exceptions\IllegalClusterNumberChange;
use EmilKlindt\MarkerClustering\Tests\Stubs\SinglePointClusteringStub;

class ClustererTest extends TestCase
{
    /** @test */
    public function can_set_max_number_of_clusters()
    {
        $clustrer = new Clusterer();

        $clustrer->setMaxNumberOfClusters($max = 3);

        $this->assertEquals($max, $clustrer->getMaxNumberOfClusters());
    }

    /** @test */
    public function cannot_set_max_number_of_clusters_when_clusters_exist()
    {
        $clustrer = new Clusterer(new SinglePointClusteringStub());

        $clustrer->addPoint(new PointStub(55.707916, 9.532549));

        $this->expectException(IllegalClusterNumberChange::class);

        $clustrer->setMaxNumberOfClusters(3);

        $this->assertEquals(0, $clustrer->getMaxNumberOfClusters());
    }

    /** @test */
    public function can_add_points_to_clusters()
    {
        $point = new PointStub(55.707916, 9.532549);

        $algorithm = new SinglePointClusteringStub();
        $clustrer = new Clusterer($algorithm);

        $clustrer->addPoint($point);

        $this->assertCount(1, $clustrer->getClusters());
        $this->assertContainsOnlyInstancesOf(Cluster::class, $clustrer->getClusters());
    }
}
