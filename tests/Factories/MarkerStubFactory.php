<?php

namespace EmilKlindt\MarkerClusterer\Test\Factories;

use EmilKlindt\MarkerClusterer\Test\Stubs\MarkerStub;
use EmilKlindt\MarkerClusterer\Test\Factories\Factory;

class MarkerStubFactory extends Factory
{
    /**
     * Model to be created.
     */
    protected string $model = MarkerStub::class;

    /**
     * Define the data used for model.
     */
    public function define(): array
    {
        return [
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude()
        ];
    }
}
