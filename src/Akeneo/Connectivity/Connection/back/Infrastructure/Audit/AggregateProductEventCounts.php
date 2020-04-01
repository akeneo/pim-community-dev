<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit;

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
    public static function normalize(array $periodEventCounts, \DateTimeZone $dateTimeZone)
    {
        return array_reduce(
            $periodEventCounts,
            function (array $data, PeriodEventCount $periodEventCount) use ($dateTimeZone) {
                $dailyEventCounts = self::groupHourlyEventCountsByDay(
                    $periodEventCount->hourlyEventCounts(),
                    $dateTimeZone
                );

                $data[$periodEventCount->connectionCode()] = self::normalizeDailyEventCounts(
                    $dailyEventCounts,
                    $periodEventCount->fromDateTime()->setTimezone($dateTimeZone),
                    $periodEventCount->upToDateTime()->setTimezone($dateTimeZone),
                );

                return $data;
            },
            []
        );
    }

    /**
     * @param HourlyEventCount[] $hourlyEventCounts
     *
     * @return array ['2020-01-01' => 3, '2020-01-03' => 6]
     */
    private static function groupHourlyEventCountsByDay(array $hourlyEventCounts, \DateTimeZone $dateTimeZone): array
    {
        return array_reduce(
            $hourlyEventCounts,
            function (array $dailyEventCounts, HourlyEventCount $hourlyEventCount) use ($dateTimeZone) {
                $eventDate = $hourlyEventCount->dateTime()->setTimezone($dateTimeZone)->format('Y-m-d');

                if (false === isset($dailyEventCounts[$eventDate])) {
                    $dailyEventCounts[$eventDate] = 0;
                }
                $dailyEventCounts[$eventDate] += $hourlyEventCount->count();

                return $dailyEventCounts;
            },
            []
        );
    }

    /**
     * @param array $dailyEventCounts = ['2020-01-01' => 3, '2020-01-03' => 6]
     *
     * @return array [
     *      [2020-01-01, 3],
     *      [2020-01-02, 0],
     *      [2020-01-03, 6],
     * ]
     */
    private static function normalizeDailyEventCounts(
        array $dailyEventCounts,
        \DateTimeImmutable $startDateTime,
        \DateTimeImmutable $endDateTime
    ): array {
        $period = new \DatePeriod($startDateTime, new \DateInterval('P1D'), $endDateTime);

        $data = [];
        foreach ($period as $dateTime) {
            $date = $dateTime->format('Y-m-d');

            $data[] = [$date, (int) ($dailyEventCounts[$date] ?? 0)];
        }

        return $data;
    }
}
