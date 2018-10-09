<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryCountQuery implements CountQuery
{
    /** @var int */
    private $volume;

    /** @var int*/
    private $limit;

    /** @var string */
    private $volumeName;

    /**
     * @param string $volumeName
     */
    public function __construct(string $volumeName)
    {
        $this->volumeName = $volumeName;
        $this->volume = -1;
        $this->limit = -1;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): CountVolume
    {
        return new CountVolume($this->volume, $this->limit, $this->volumeName);
    }

    /**
     * @param int $volume
     */
    public function setVolume(int $volume): void
    {
        $this->volume = $volume;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
