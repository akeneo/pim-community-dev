<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionHandler
{
    /** @var SelectConnectionsEventCountByDayQuery */
    private $selectConnectionsEventCountByDayQuery;

    public function __construct(SelectConnectionsEventCountByDayQuery $selectConnectionsEventCountByDayQuery)
    {
        $this->selectConnectionsEventCountByDayQuery = $selectConnectionsEventCountByDayQuery;
    }

    public function handle(CountDailyEventsByConnectionQuery $query): array
    {
        [$fromUtcDateTime, $upToUtcDateTime] = $this->createUtcDateTimeInterval(
            $query->startDate(),
            $query->endDate(),
            $query->timezone()
        );

        $hourlyEventsPerConnection = $this
            ->selectConnectionsEventCountByDayQuery
            ->execute($query->eventType(), $fromUtcDateTime, $upToUtcDateTime);

        $weeklyEventCounts = [];
        foreach ($hourlyEventsPerConnection as $connectionCode => $hourlyEventCounts) {
            $weeklyEventCounts[] = new WeeklyEventCounts(
                $connectionCode,
                $query->startDate(),
                $query->endDate(),
                $query->timezone(),
                $hourlyEventCounts
            );
        }

        return $weeklyEventCounts;
    }

    private function createUtcDateTimeInterval(string $startDate, string $endDate, string $timezone): array
    {
        $dateTimeZone = new \DateTimeZone($timezone);

        $fromDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate, $dateTimeZone);
        if (false === $fromDateTime) {
            throw new \RuntimeException();
        }
        $fromDateTime = $fromDateTime
            ->setTime(0, 0)
            ->setTimezone(new \DateTimeZone('UTC'));

        $upToDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate, $dateTimeZone);
        if (false === $upToDateTime) {
            throw new \RuntimeException();
        }
        $upToDateTime = $upToDateTime
            ->setTime(0, 0)
            ->add(new \DateInterval('P1D'))
            ->setTimezone(new \DateTimeZone('UTC'));

        return [$fromDateTime, $upToDateTime];
    }
}
