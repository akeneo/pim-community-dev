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

    public function __construct(string $connectionCode, string $startDate, string $endDate, array $eventData)
    {
        $this->connectionCode = $connectionCode;
        $this->dailyEventCounts = $this->hydrateDailyEventCounts(
            new \DateTime($startDate, new \DateTimeZone('UTC')),
            new \DateTime($endDate, new \DateTimeZone('UTC')),
            $eventData
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

    private function hydrateDailyEventCounts(\DateTime $start, \DateTime $end, $eventData)
    {
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end->modify('+1 day')
        );

        $dailyEventCounts = [];
        foreach ($period as $date) {
            $count = $eventData[$date->format('Y-m-d')] ?? 0;

            $dailyEventCounts[] = new DailyEventCount(
                (int) $count,
                $date
            );
        }

        return $dailyEventCounts;
    }
}
