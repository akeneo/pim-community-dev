<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model;

/**
 * Represents a previously aggregated volume.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedVolume
{
    /** @var string */
    private $volumeName;

    /** @var array */
    private $volume;

    /** @var \DateTime */
    private $aggregatedAt;

    /**
     * @param string    $volumeName
     * @param array     $volume
     * @param \DateTime $aggregatedAt
     */
    public function __construct(string $volumeName, array $volume, \DateTime $aggregatedAt)
    {
        $this->volumeName = $volumeName;
        $this->volume = $volume;
        $this->aggregatedAt = $aggregatedAt;
    }

    /**
     * @return string
     */
    public function getVolumeName(): string
    {
        return $this->volumeName;
    }

    /**
     * @return array
     */
    public function getVolume(): array
    {
        return $this->volume;
    }

    /**
     * @return \DateTime
     */
    public function aggregatedAt(): \DateTime
    {
        return $this->aggregatedAt;
    }
}
