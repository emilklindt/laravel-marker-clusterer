<?php

namespace EmilKlindt\MarkerClusterer\Test;

use Faker\Generator as Faker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Test\Factories\ConfigFactory;
use EmilKlindt\MarkerClusterer\Test\Factories\MarkerStubFactory;

class TestCase extends BaseTestCase
{
    private Faker $faker;

    protected ConfigFactory $configFactory;
    protected MarkerStubFactory $markerStubFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();

        $this->configFactory = new ConfigFactory($this->faker);
        $this->markerStubFactory = new MarkerStubFactory($this->faker);
    }

    protected function makeMarker(?array $attributes = null): Clusterable
    {
        return $this->markerStubFactory
            ->make($attributes);
    }

    protected function markTestAsPassed(): void
    {
        $this->assertTrue(true);
    }
}
