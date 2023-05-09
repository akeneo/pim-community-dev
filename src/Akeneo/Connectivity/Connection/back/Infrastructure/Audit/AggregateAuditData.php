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
final class AggregateAuditData
{
    /**
     * @param PeriodEventCount[] $periodEventCounts
     *
     * @return array [
     *      [$connectionCode] => [
     *          'previous_week' => [
     *              '2020-01-01' => 3,
     *          ],
     *          'current_week' => [
     *              '2020-01-02' => 0,
     *              '2020-01-03' => 6,
     *          ],
     *          'current_week_total' => 6
     *      ]
     * ]
     */
    public static function normalize(array $periodEventCounts, \DateTimeZone $dateTimeZone): array
    {
        return \array_reduce(
            $periodEventCounts,
            function (array $data, PeriodEventCount $periodEventCount) use ($dateTimeZone): array {
                $dailyEventCounts = self::groupHourlyEventCountByDay(
                    $periodEventCount->hourlyEventCounts(),
                    $dateTimeZone
                );


                $dailyEventCounts = self::fillMissingDays(
                    $dailyEventCounts,
                    $periodEventCount->fromDateTime()->setTimezone($dateTimeZone),
                    $periodEventCount->upToDateTime()->setTimezone($dateTimeZone),
                );

                $previousWeekEventCounts = \array_slice($dailyEventCounts, 0, 1);
                $currentWeekEventCounts = \array_slice($dailyEventCounts, 1);

                $data[$periodEventCount->connectionCode()] = [
                    'previous_week' => $previousWeekEventCounts,
                    'current_week' => $currentWeekEventCounts,
                    'current_week_total' => \array_sum($currentWeekEventCounts)
                ];

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
    private static function groupHourlyEventCountByDay(array $hourlyEventCounts, \DateTimeZone $dateTimeZone): array
    {
        return \array_reduce(
            $hourlyEventCounts,
            function (array $dailyEventCounts, HourlyEventCount $hourlyEventCount) use ($dateTimeZone): array {
                $eventDate = $hourlyEventCount->dateTime()->setTimezone($dateTimeZone)->format('Y-m-d');

                if (!isset($dailyEventCounts[$eventDate])) {
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
     * @return array ['2020-01-01' => 3, '2020-01-02' => 0, '2020-01-03' => 6]
     */
    private static function fillMissingDays(
        array $dailyEventCounts,
        \DateTimeImmutable $startDateTime,
        \DateTimeImmutable $endDateTime
    ): array {
        $period = new \DatePeriod($startDateTime, new \DateInterval('P1D'), $endDateTime);

        $data = [];
        foreach ($period as $dateTime) {
            $date = $dateTime->format('Y-m-d');

            $data[$date] = (int) ($dailyEventCounts[$date] ?? 0);
        }

        return $data;
    }
}
