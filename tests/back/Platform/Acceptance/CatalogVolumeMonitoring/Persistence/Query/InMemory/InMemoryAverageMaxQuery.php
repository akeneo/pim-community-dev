<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAverageMaxQuery implements AverageMaxQuery
{
    /** @var int */
    private $limit;

    /** @var string */
    private $volumeName;

    /** @var array */
    private $values = [];

    /**
     * @param string $volumeName
     */
    public function __construct(string $volumeName)
    {
        $this->volumeName = $volumeName;
        $this->limit = -1;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        $averageVolume = empty($this->values) ? 0 : intval(array_sum($this->values) / count($this->values));
        $maxVolume =  empty($this->values) ? 0 : max($this->values);

        return new AverageMaxVolumes($maxVolume, $averageVolume, $this->limit, $this->volumeName);
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @param int $value
     */
    public function addValue(int $value): void
    {
        $this->values[] = $value;
    }
}
