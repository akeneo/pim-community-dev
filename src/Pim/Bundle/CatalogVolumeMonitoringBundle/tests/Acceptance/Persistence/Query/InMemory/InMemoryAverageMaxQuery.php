<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory;

use Pim\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAverageMaxQuery implements AverageMaxQuery
{
    /** @var int */
    private $averageVolume;

    /** @var int */
    private $maxVolume;

    /** @var int */
    private $limit;

    /** @var string */
    private $volumeName;

    /**
     * @param string $volumeName
     */
    public function __construct(string $volumeName)
    {
        $this->volumeName = $volumeName;
        $this->averageVolume = -1;
        $this->maxVolume = -1;
        $this->limit = -1;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        return new AverageMaxVolumes($this->maxVolume, $this->averageVolume, $this->limit, $this->volumeName);
    }

    /**
     * @param int $averageVolume
     */
    public function setAverageVolume(int $averageVolume): void
    {
        $this->averageVolume = $averageVolume;
    }

    /**
     * @param int $maxVolume
     */
    public function setMaxVolume(int $maxVolume): void
    {
        $this->maxVolume = $maxVolume;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
