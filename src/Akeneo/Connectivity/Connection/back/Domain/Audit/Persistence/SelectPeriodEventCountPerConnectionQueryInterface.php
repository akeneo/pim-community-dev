<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectPeriodEventCountPerConnectionQueryInterface
{
    /**
     * Select hourly event counts per connection AND the sum for all the connections (with the code '<all>') for a given
     * period.
     *
     * @param string $eventType Value from the EventTypes enum
     *
     * @return PeriodEventCount[]
     */
    public function execute(
        string $eventType,
        DateTimePeriod $period
    ): array;
}
