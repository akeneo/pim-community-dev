<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

/**
 * Represents the average volume and maximum volume for a given entity.
 *
 * For example, the maximum number of attributes per family, among all the families,
 * and the average number of attributes per family.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxVolumes
{
    /** @var int */
    private $maxVolume;

    /** @var int */
    private $averageVolume;

    /**
     * @param int    $maxVolume
     * @param int    $averageVolume
     * @param int    $limit
     * @param string $volumeName
     */
    public function __construct(int $maxVolume, int $averageVolume)
    {
        $this->maxVolume = $maxVolume;
        $this->averageVolume = $averageVolume;
    }

    public function getMaxVolume(): int
    {
        return $this->maxVolume;
    }

    public function getAverageVolume(): int
    {
        return $this->averageVolume;
    }
}
