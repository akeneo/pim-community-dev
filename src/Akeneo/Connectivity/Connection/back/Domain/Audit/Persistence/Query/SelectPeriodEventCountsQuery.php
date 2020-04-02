<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectConnectionsEventCountByDayQuery
{
    /**
     * Select hourly event counts per connection AND the sum for all the connections (with the code '<all>').
     *
     * @param string $eventType Value from the EventTypes enum
     * @param \DateTimeImmutable $fromDateTime Starting from $fromDateTime
     * @param \DateTimeImmutable $upToDateTime Ending before $upToDateTime ($upToDateTime not included)
     *
     * @return PeriodEventCount[]
     */
    public function execute(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): array;
}
