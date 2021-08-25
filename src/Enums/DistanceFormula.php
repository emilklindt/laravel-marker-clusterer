<?php

namespace EmilKlindt\MarkerClusterer\Enums;

use EmilKlindt\MarkerClusterer\Enums\BaseEnum;

/**
 * @see thephpleague/geotools
 */
final class DistanceFormula extends BaseEnum
{
    const FLAT = 'flat';
    const GREAT_CIRCLE = 'greatCircle';
    const HAVERSINE = 'haversine';
    const VINCENTY = 'vincenty';
}
