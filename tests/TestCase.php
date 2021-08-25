<?php

namespace EmilKlindt\MarkerClusterer\Test;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function markTestAsPassed(): void
    {
        $this->assertTrue(true);
    }
}
