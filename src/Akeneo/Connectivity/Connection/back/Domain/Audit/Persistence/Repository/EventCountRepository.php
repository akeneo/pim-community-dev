<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface EventCountRepository
{
    /**
     * @param HourlyEventCount[] $hourlyEventCounts
     */
    public function bulkInsert(array $hourlyEventCounts): void;

    public function upsert(HourlyEventCount $hourlyEventCounts): void;
}
