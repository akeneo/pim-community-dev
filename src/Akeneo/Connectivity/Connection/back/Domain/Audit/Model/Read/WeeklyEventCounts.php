<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class WeeklyEventCounts
{
    /** @var string */
    private $connectionCode;

    /** @var DailyEventCount[] */
    private $dailyEventCounts = [];

    /**
     * @param string $startDate Format 'Y-m-d'
     * @param string $endDate Format 'Y-m-d'
     * @param string $timezone Timezone for the startDate and endDate
     * @param array $hourlyEventCounts
     * Type: { '<all>': Array<[DateTime, int]>, [connectionCode: string]: Array<[DateTime, int]> }
     */
    public function __construct(
        string $connectionCode,
        string $startDate,
        string $endDate,
        string $timezone,
        array $hourlyEventCounts
    ) {
        $startDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate, new \DateTimeZone($timezone));
        if (false === $startDateTime) {
            throw new \RuntimeException();
        }
        $endDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate, new \DateTimeZone($timezone));
        if (false === $endDateTime) {
            throw new \RuntimeException();
        }

        $this->connectionCode = $connectionCode;

        $dailyTimezonedEventCountsData = $this->groupByDailyTimezonedEventCount(
            $hourlyEventCounts,
            new \DateTimeZone($timezone)
        );
        $this->dailyEventCounts = $this->hydrateDailyEventCounts(
            $startDateTime,
            $endDateTime,
            $dailyTimezonedEventCountsData
        );
    }

    public function normalize()
    {
        return [
            $this->connectionCode => \array_reduce(
                $this->dailyEventCounts,
                function (array $weeklyEventCounts, DailyEventCount $dailyEventCount) {
                    return array_merge($weeklyEventCounts, $dailyEventCount->normalize());
                },
                []
            ),
        ];
    }

    private function groupByDailyTimezonedEventCount(array $hourlyEvents, \DateTimeZone $dateTimeZone): array
    {
        return array_reduce($hourlyEvents, function (array $dailyEvents, array $hourlyEvent) use ($dateTimeZone) {
            [$eventDateTime, $eventCount] = $hourlyEvent;

            $eventDate = $eventDateTime->setTimezone($dateTimeZone)->format('Y-m-d');

            if (false === isset($dailyEvents[$eventDate])) {
                $dailyEvents[$eventDate] = 0;
            }
            $dailyEvents[$eventDate] += $eventCount;

            return $dailyEvents;
        }, []);
    }

    private function hydrateDailyEventCounts(\DateTimeImmutable $start, \DateTimeImmutable $end, $eventData)
    {
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end->modify('+1 day')
        );

        $dailyEventCounts = [];
        foreach ($period as $dateTime) {
            $date = $dateTime->format('Y-m-d');

            $dailyEventCounts[] = new DailyEventCount($date, (int) ($eventData[$date] ?? 0));
        }

        return $dailyEventCounts;
    }
}
