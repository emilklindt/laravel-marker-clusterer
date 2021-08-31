<?php

namespace EmilKlindt\MarkerClusterer\Enums;

use EmilKlindt\MarkerClusterer\Enums\BaseEnum;

/**
 * @see thephpleague/geotools
 */
final class DistanceFormula extends BaseEnum
{
    // geotools
    const FLAT = 'flat';
    const HAVERSINE = 'haversine';
    const GREAT_CIRCLE = 'greatCircle';

    // custom
    const MANHATTAN = 'manhattan';
}
