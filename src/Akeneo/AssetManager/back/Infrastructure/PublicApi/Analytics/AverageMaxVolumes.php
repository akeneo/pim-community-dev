<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxVolumes
{
    private int $maxVolume;

    private int $averageVolume;

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
