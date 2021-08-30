<?php

namespace EmilKlindt\MarkerClusterer\Test\Factories;

use Faker\Generator as Faker;

abstract class Factory {
    /**
     * Model to be created.
     */
    protected string $model;

    /**
     * Faker instance used for dummy data.
     */
    protected Faker $faker;

    /**
     * Create a new instance of the factory.
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Create a new instance of model.
     */
    public function make(?array $attributes = null)
    {
        $attributes = $attributes ?: [];

        return new $this->model(array_merge($this->define(), $attributes));
    }

    /**
     * Define the data used for model.
     */
    protected abstract function define(): array;
}
