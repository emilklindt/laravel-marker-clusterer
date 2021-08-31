<?php

namespace EmilKlindt\MarkerClusterer\Test\Factories;

use EmilKlindt\MarkerClusterer\Models\Config;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Test\Factories\Factory;

class ConfigFactory extends Factory
{
    /**
     * Model to be created.
     */
    protected string $model = Config::class;

    /**
     * Define the data used for model.
     */
    public function define(): array
    {
        return [
            'k' => $this->faker->randomDigitNotZero(),
            'includeNoise' => $this->faker->boolean(),
            'samples' => $this->faker->randomDigitNotZero(),
            'iterations' => $this->faker->randomDigitNotZero(),
            'minSamples' => $this->faker->randomDigitNotZero(),
            'epsilon' => $this->faker->randomFloat(2, 0, 1000),
            'convergenceMaximum' => $this->faker->randomFloat(2, 0, 1000),
            'distanceFormula' => $this->faker->randomElement(DistanceFormula::getConstants()),
        ];
    }
}
