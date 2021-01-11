<?php

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQuery;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDelayUntilNextRequest
{
    private SelectEventsApiRequestCountWithinLastHourQuery $selectEventsApiRequestCountWithinLastHourQuery;

    public function __construct(
        SelectEventsApiRequestCountWithinLastHourQuery $selectEventsApiRequestCountWithinLastHourQuery
    ) {
        $this->selectEventsApiRequestCountWithinLastHourQuery = $selectEventsApiRequestCountWithinLastHourQuery;
    }

    public function execute(\DateTimeImmutable $dateTime, $limit): int
    {
        $eventsApiRequestCountWithinLastHour = $this->selectEventsApiRequestCountWithinLastHourQuery->execute($dateTime);

        $count = 0;
        foreach ($eventsApiRequestCountWithinLastHour as $currentEventsApiRequestCount) {
            $count += $currentEventsApiRequestCount['event_count'];

            if ($count >= $limit) {
                $lastDateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $currentEventsApiRequestCount['updated']);

                return 3600 - ($dateTime->getTimestamp() - $lastDateTime->getTimestamp());
            }
        }

        return 0;
    }
}

