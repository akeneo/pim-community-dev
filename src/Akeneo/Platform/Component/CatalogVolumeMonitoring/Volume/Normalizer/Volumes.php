<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Normalizer;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Volumes
{
    /** @var AverageMaxVolumesNormalizer */
    private $averageMaxVolumesNormalizer;

    /** @var CountVolumeNormalizer */
    private $countVolumesNormalizer;

    /** @var array */
    private $countQueries;

    /** @var array */
    private $averageMaxQueries;

    /**
     * @param CountVolumeNormalizer       $countVolumesNormalizer
     * @param AverageMaxVolumesNormalizer $averageMaxVolumesNormalizer
     * @param CountQuery[]                $countQueries
     * @param AverageMaxQuery[]           $averageMaxQueries
     */
    public function __construct(
        CountVolumeNormalizer $countVolumesNormalizer,
        AverageMaxVolumesNormalizer $averageMaxVolumesNormalizer,
        iterable $countQueries = [],
        iterable $averageMaxQueries = []
    ) {
        $this->countVolumesNormalizer = $countVolumesNormalizer;
        $this->averageMaxVolumesNormalizer = $averageMaxVolumesNormalizer;
        $this->countQueries = $countQueries;
        $this->averageMaxQueries = $averageMaxQueries;
    }

    /**
     * Returns an array containing the volume values of the different entities.
     *
     * @return array
     */
    public function volumes(): array
    {
        $data = [];

        foreach ($this->countQueries as $query) {
            $queryResponse = $query->fetch();
            $data = array_merge($data, $this->countVolumesNormalizer->normalize($queryResponse));
        }

        foreach ($this->averageMaxQueries as $query) {
            $queryResponse = $query->fetch();
            $data = array_merge($data, $this->averageMaxVolumesNormalizer->normalize($queryResponse));
        }

        return $data;
    }
}
