<?php

declare(strict_types=1);

namespace Pim\Component\CatalogVolumeMonitoring\Volume\Repository;

use Pim\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

/**
 * Repository for the aggregated volumes.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AggregatedVolumeRepositoryInterface
{
    /**
     * @param AggregatedVolume $aggregatedVolume
     */
    public function add(AggregatedVolume $aggregatedVolume): void;
}
