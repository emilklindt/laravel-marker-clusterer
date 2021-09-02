<?php

namespace EmilKlindt\MarkerClusterer\Support;

/**
 * This is a helper method comprised of modified code, originally
 * from the Geohash repository by skthon. See source below.
 *
 * @source https://github.com/skthon/geohash/blob/master/src/Geohash.php
 */
class GeohashHelper
{
    private const BASE_32_MAPPING = '0123456789bcdefghjkmnpqrstuvwxyz';

    private const NORTH = 0;
    private const EAST = 1;
    private const SOUTH = 2;
    private const WEST = 3;

    const EVEN = 0;
    const ODD = 1;

    private const BORDER_CHARS = [
        self::EVEN => [
            self::NORTH => 'bcfguvyz',
            self::EAST => 'prxz',
            self::SOUTH => '0145hjnp',
            self::WEST => '028b',
        ],
        self::ODD => [
            self::NORTH => 'prxz',
            self::EAST => 'bcfguvyz',
            self::SOUTH => '028b',
            self::WEST => '0145hjnp',
        ]
    ];

    private const NEIGHBOR_CHARS = [
        self::EVEN => [
            self::NORTH => '238967debc01fg45kmstqrwxuvhjyznp',
            self::EAST => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
            self::SOUTH => 'bc01fg45238967deuvhjyznpkmstqrwx',
            self::WEST => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
        ],
        self::ODD => [
            self::NORTH => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
            self::EAST => '238967debc01fg45kmstqrwxuvhjyznp',
            self::SOUTH => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
            self::WEST => 'bc01fg45238967deuvhjyznpkmstqrwx',
        ],
    ];

    /**
     * Computes neighboring geohash values for given geohash.
     */
    public static function getNeighbors(string $hash): array
    {
        $hashNorth = self::calculateNeighbor($hash, self::NORTH);
        $hashEast = self::calculateNeighbor($hash, self::EAST);
        $hashSouth = self::calculateNeighbor($hash, self::SOUTH);
        $hashWest = self::calculateNeighbor($hash, self::WEST);

        $hashNorthEast = self::calculateNeighbor($hashNorth, self::EAST);
        $hashSouthEast = self::calculateNeighbor($hashSouth, self::EAST);
        $hashSouthWest = self::calculateNeighbor($hashSouth, self::WEST);
        $hashNorthWest = self::calculateNeighbor($hashNorth, self::WEST);

        return [
            $hashNorth,
            $hashEast,
            $hashSouth,
            $hashWest,
            $hashNorthEast,
            $hashSouthEast,
            $hashSouthWest,
            $hashNorthWest,
        ];
    }

    /**
     * Calculates neighbor geohash for given geohash and direction
     */
    public static function calculateNeighbor(string $hash, string $direction): string
    {
        $length = strlen($hash);

        if ($length == 0) {
            return '';
        }

        $lastChar = $hash[$length - 1];
        $evenOrOdd = ($length - 1) % 2;
        $baseHash = substr($hash, 0, -1);

        if (strpos(self::BORDER_CHARS[$evenOrOdd][$direction], $lastChar) !== false) {
            $baseHash = self::calculateNeighbor($baseHash, $direction);
        }

        return $baseHash . self::NEIGHBOR_CHARS[$evenOrOdd][$direction][strpos(self::BASE_32_MAPPING, $lastChar)];
    }
}
