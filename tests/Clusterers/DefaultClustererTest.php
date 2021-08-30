<?php

namespace EmilKlindt\MarkerClusterer\Test\Clusterers;

use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use EmilKlindt\MarkerClusterer\BaseClusterer;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Test\Stubs\MarkerStub;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Clusterers\DefaultClusterer;
use EmilKlindt\MarkerClusterer\Test\Stubs\TestClustererStub;

class DefaultClustererTest extends TestCase
{
    /** @test */
    public function it_correctly_proxies_constructor_methods()
    {
        /** @var BaseClusterer|MockInterface */
        $mock = Mockery::mock('TestClusterer', BaseClusterer::class)
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('setup');

        new DefaultClusterer(null, $mock);
    }

    /** @test */
    public function it_correctly_proxies_getters_and_setters()
    {
        /** @var BaseClusterer|MockInterface */
        $mock = Mockery::mock('TestClusterer', BaseClusterer::class)
            ->shouldAllowMockingProtectedMethods();

        $clusterer = new DefaultClusterer(null, $mock);

        $mock->expects()
            ->addMarker()
            ->withArgs(function ($marker) {
                return $marker instanceof Clusterable;
            });

        $clusterer->addMarker(new MarkerStub(55.12, 9.56));

        $mock->expects()
            ->getClusters()
            ->andReturn(new Collection());

        $clusterer->getClusters();

    }
}
