<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel;

/**
 * Represents the volume of an axis of limitation.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVolume
{
    /** @var int */
    private $volume;

    /** @var int*/
    private $limit;

    /** @var string */
    private $volumeName;

    /**
     * @param int    $volume
     * @param int    $limit
     * @param string $volumeName
     */
    public function __construct(int $volume, int $limit, string $volumeName)
    {
        $this->volume = $volume;
        $this->limit = $limit;
        $this->volumeName = $volumeName;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function getVolumeName(): string
    {
        return $this->volumeName;
    }

    public function hasWarning(): bool
    {
        return $this->limit >= 0 && $this->volume > $this->limit;
    }
}
