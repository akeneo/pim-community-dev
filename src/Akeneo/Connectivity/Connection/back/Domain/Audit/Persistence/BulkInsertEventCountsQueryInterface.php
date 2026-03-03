<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BulkInsertEventCountsQueryInterface
{
    /**
     * @param HourlyEventCount[] $hourlyEventCounts
     */
    public function execute(array $hourlyEventCounts): void;
}
