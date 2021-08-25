<?php

namespace EmilKlindt\MarkerClusterer\Enums;

use ReflectionClass;

abstract class BaseEnum
{
    static function getConstants(): array
    {
        $reflection = new ReflectionClass(get_called_class());
        return $reflection->getConstants();
    }
}
