<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AggregateProductEventCounts
{
    /**
     * @param PeriodEventCount[] $periodEventCounts
     */
    public function normalize(
        \DateTimeImmutable $startDateTimeUser,
        \DateTimeImmutable $endDateTimeUser,
        \DateTimeZone $timezone,
        array $periodEventCounts
    ) {
        $data = [];
        foreach ($periodEventCounts as $periodEventCount) {
            $dailyEventCounts = $this->groupHourlyEventCountsByDay(
                $periodEventCount->hourlyEventCounts(),
                $timezone
            );

            $data[$periodEventCount->connectionCode()] = $this->normalizeDailyEventCounts(
                $dailyEventCounts,
                $startDateTimeUser,
                $endDateTimeUser
            );
        }

        return $data;




//        $dailyEventCounts = \array_reduce(
//            $weeklyEventCountsPerConnection,
//            function (array $data, WeeklyEventCounts $weeklyEventCounts) use ($startDateTime, $endDateTime, $timezone) {
//                return array_merge(
//                    $data,
//                    $this->normalizeWeeklyEventCounts(
//                        $startDateTime,
//                        $endDateTime,
//                        $timezone,
//                        $weeklyEventCounts->hourlyEventCountsPerConnection()
//                    )
//                );
//            },
//            []
//        );
//
//        return [
//            $weeklyEventCounts->connectionCode() => \array_reduce(
//                $dailyEventCounts,
//                function (array $weeklyEventCounts, DailyEventCount $dailyEventCount) {
//                    return array_merge($weeklyEventCounts, $dailyEventCount->normalize());
//                },
//                []
//            ),
//        ];
    }

    private function normalizeDailyEventCounts(
        array $dailyEventCounts,
        \DateTimeImmutable $startDateTime,
        \DateTimeImmutable $endDateTime
    ): array {
        $period = new \DatePeriod(
            $startDateTime,
            new \DateInterval('P1D'),
            $endDateTime->modify('+1 day')
        );

        $data = [];
        foreach ($period as $dateTime) {
            $date = $dateTime->format('Y-m-d');

            $data[] = [$date, (int) ($dailyEventCounts[$date] ?? 0)];
        }

        return $data;
    }

    /**
     * @param HourlyEventCount[] $hourlyEventCounts
     */
    private function groupHourlyEventCountsByDay(array $hourlyEventCounts, \DateTimeZone $dateTimeZone): array
    {
        return array_reduce($hourlyEventCounts, function (array $dailyEventCounts, HourlyEventCount $hourlyEventCount) use ($dateTimeZone) {
            $eventDate = $hourlyEventCount->dateTime()->setTimezone($dateTimeZone)->format('Y-m-d');

            if (false === isset($dailyEventCounts[$eventDate])) {
                $dailyEvents[$eventDate] = 0;
            }
            $dailyEventCounts[$eventDate] += $hourlyEventCount->count();

            return $dailyEventCounts;
        }, []);
    }
}
