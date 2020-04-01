<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

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
     * @param \DateTimeInterface $fromDateTime Starting from $fromDateTime
     * @param \DateTimeInterface $upToDateTime Ending before $upToDateTime ($upToDateTime not included)
     *
     * @return array ['<all>' => HourlyEventCount[], $connectionCode => HourlyEventCount[]]
     */
    public function execute(
        string $eventType,
        \DateTimeInterface $fromDateTime,
        \DateTimeInterface $upToDateTime
    ): array;
}
