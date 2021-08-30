<?php

namespace EmilKlindt\MarkerClusterer\Test\Support;

use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use InvalidArgumentException;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\Test\TestCase;
use EmilKlindt\MarkerClusterer\Support\DistanceCalculator;

class DistanceCalculatorTest extends TestCase
{
    public function geotoolsDistanceFormulasProvider(): array
    {
        return [
            [DistanceFormula::FLAT],
            [DistanceFormula::VINCENTY],
            [DistanceFormula::HAVERSINE],
            [DistanceFormula::GREAT_CIRCLE],
        ];
    }

    /** @test */
    public function it_throws_exception_for_invalid_distance_formula()
    {
        $this->expectException(InvalidArgumentException::class);

        new DistanceCalculator('invalid');
    }

    /**
     * @test
     * @dataProvider geotoolsDistanceFormulasProvider
     */
    public function it_calculates_distance_correctly_using_geotools(string $formula)
    {
        $from = new Coordinate([
            55.439657,
            11.791899
        ]);

        $to = new Coordinate([
            56.953879,
            8.685348
        ]);

        $calculator = new DistanceCalculator($formula);
        $distance = $calculator->measure($from, $to);

        $this->assertEqualsWithDelta(255745, $distance, 500);
    }

    /** @test */
    public function it_calculates_manhattan_distance_correctly()
    {
        $from = new Coordinate([
            55.439657,
            11.791899
        ]);

        $to = new Coordinate([
            56.953879,
            8.685348
        ]);

        $calculator = new DistanceCalculator(DistanceFormula::MANHATTAN);
        $distance = $calculator->measure($from, $to);

        $this->assertEqualsWithDelta(440888, $distance, 100);
    }
}
