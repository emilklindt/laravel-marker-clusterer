<?php

namespace EmilKlindt\MarkerClusterer\Clusterers;

use Illuminate\Support\Collection;
use League\Geotools\Geohash\Geohash;
use League\Geotools\Coordinate\Coordinate;
use EmilKlindt\MarkerClusterer\BaseClusterer;
use EmilKlindt\MarkerClusterer\Models\Cluster;
use EmilKlindt\MarkerClusterer\Enums\DistanceFormula;
use EmilKlindt\MarkerClusterer\Support\GeohashHelper;
use EmilKlindt\MarkerClusterer\Interfaces\Clusterable;
use EmilKlindt\MarkerClusterer\Support\GeohashNeighbor;
use EmilKlindt\MarkerClusterer\Traits\HasDistanceMatrix;

class DensityBasedSpatialClusterer extends BaseClusterer
{
    private const NOISE = -1;

    public const GEOHASH_LENGTH_METERS_MAP = [
        1 => 2500000,
        2 => 630000,
        3 => 78000,
        4 => 20000,
        5 => 2400,
        6 => 610,
        7 => 76,
        8 => 19,
        9 => 2.4,
        10 => 0.6,
        11 => 0.074,
    ];

    /**
     * Cluster counter, to keep track of id.
     */
    private int $n;

    /**
     * Array to keep track of node labels, by index.
     */
    private array $labels;

    /**
     * Array to store geohashes for added markers.
     */
    private array $geohashes;

    /**
     * Length of the geohashes, determined by epsilon.
     */
    private int $geohashLength;

    /**
     * Merge the provided config with default values.
     */
    protected function mergeDefaultConfig(): void
    {
        $this->setDefaultConfigValue(
            'useGeohashNeighboring',
            config('marker-clusterer.dbscan.default_use_geohash_neighboring')
        );

        $this->setDefaultConfigValue(
            'distanceFormula',
            config('marker-clusterer.default_distance_formula')
        );

        $this->setDefaultConfigValue(
            'includeNoise',
            config('marker-clusterer.dbscan.default_include_noise')
        );
    }

    /**
     * Perform necessary setup of the algorithm.
     */
    protected function setup(): void
    {
        $this->geohashes = [];
    }

    /**
     * A more precise geohash will include less results in the
     * neighboring search, and therefore result in faster over-
     * all performance of the clustering
     */
    private function setGeohashLengthFromEpsilon(): void
    {
        foreach (self::GEOHASH_LENGTH_METERS_MAP as $geohashLength => $maxErrorMeters) {
            if ($maxErrorMeters < $this->config->epsilon) {
                break;
            }

            $this->geohashLength = $geohashLength;
        }
    }

    /**
     * Validate that the config is sufficient for the algorithm
     */
    protected function validateConfig(): bool
    {
        return is_float($this->config->epsilon)
            && is_int($this->config->minSamples)
            && is_bool($this->config->includeNoise)
            && in_array($this->config->distanceFormula, DistanceFormula::getConstants());
    }

    /**
     * Add a new marker to the clusterer
     */
    public function addMarker(Clusterable $marker): self
    {
        $index = $this->markers->count();

        $this->markers->put($index, $marker);
        $this->setGeohash($index, $marker->getClusterableCoordinate());

        return $this;
    }

    /**
     * Store the geohashed value of the coordinate
     */
    private function setGeohash(int $index, Coordinate $coordinate): void
    {
        if (!isset($this->geohashLength)) {
            $this->setGeohashLengthFromEpsilon();
        }

        $hasher = new Geohash();
        $hasher->encode($coordinate, $this->geohashLength);

        $hash = $hasher->getGeohash();

        if (!isset($this->geohashes[$hash])) {
            $this->geohashes[$hash] = [$index];
        } else {
            $this->geohashes[$hash][] = $index;
        }
    }

    /**
     * Get the clusters derived from the added markers
     */
    public function getClusters(): Collection
    {
        $this->clearLabels();
        $this->n = 0;

        // visit each point and expand clusters meeting sample criterion
        foreach ($this->markers as $p => $marker) {
            if (!is_null($this->labels[$p])) {
                continue;
            }

            $neighborhoodIndexes = $this->getIndexesWithinNeighborhood($p);

            if (count($neighborhoodIndexes) < $this->config->minSamples) {
                $this->labels[$p] = self::NOISE;
                continue;
            }

            $this->expandClusterNeighborhood($neighborhoodIndexes);
            $this->n++;
        }

        $this->createClustersFromLabels();
        $this->updateClusterCentroids();

        return $this->clusters;
    }

    /**
     * Continously consider all points within epsilon as part of the
     * cluster, untill no more points are within epsilon distance.
     */
    private function expandClusterNeighborhood(array $queue): void
    {
        while (($p = array_pop($queue)) !== null) {
            if (!is_null($this->labels[$p])) {
                if ($this->labels[$p] === self::NOISE) {
                    $this->labels[$p] = $this->n;
                }

                continue;
            }

            $this->labels[$p] = $this->n;

            $neighborIndexes = $this->getIndexesWithinNeighborhood($p);

            if (count($neighborIndexes) >= $this->config->minSamples) {
                $queue = array_unique(array_merge($queue, $neighborIndexes));
            }
        }
    }

    /**
     * Create a clusters based on labels
     */
    private function createClustersFromLabels(): void
    {
        $this->clusters = new Collection(array_fill(0, $this->n, null));

        $this->markers
            ->each(function (Clusterable $marker, int $p) {
                $label = $this->labels[$p];

                // avoid clustering noise, if not included by config
                if ($label === self::NOISE && !$this->config->includeNoise) {
                    return;
                }

                $cluster = $this->clusters->get($label);

                if ($cluster === null) {
                    $cluster = new Cluster([
                        'markers' => new Collection(),
                    ]);

                    $this->clusters->put($label, $cluster);
                }

                $cluster->markers->add($marker);
            });
    }

    /**
     * Reset labels for nodes, from previous runs
     */
    private function clearLabels(): void
    {
        $this->labels = array_fill(0, $this->markers->count(), null);
    }

    private function getIndexesInGeohashNeighborhood(Coordinate $origin): array
    {
        $hasher = new Geohash();
        $hasher->encode($origin, $this->geohashLength);

        $geohash = $hasher->getGeohash();
        $neighbors = GeohashHelper::getNeighbors($geohash);

        $indexes = [...$this->geohashes[$geohash]];

        foreach ($neighbors as $neighbor) {
            if (!isset($this->geohashes[$neighbor])) {
                continue;
            }

            array_push($indexes, ...$this->geohashes[$neighbor]);
        }

        return $indexes;
    }

    /**
     * Get index markers within epsilon distance of marker index
     */
    private function getIndexesWithinNeighborhood(int $origin): array
    {
        $originCoordinate = $this->markers->get($origin)->getClusterableCoordinate();
        $candidates = $this->getIndexesInGeohashNeighborhood($originCoordinate);

        // avoid further precision if geohash neighboring is used
        if ($this->config->useGeohashNeighboring === true) {
            return $candidates;
        }

        $indexes = [];

        // calculate distance to each marker, and compare with epsilon value
        foreach ($candidates as $index) {
            $candidateCoordinate = $this->markers->get($index)->getClusterableCoordinate();

            if ($this->distanceCalculator->measure($originCoordinate, $candidateCoordinate) < $this->config->epsilon) {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }
}
