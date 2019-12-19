<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
use Akeneo\Apps\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Apps\Domain\Audit\Persistence\Query\SelectAppsEventCountByDayQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppsEventCountByDayQuery implements SelectAppsEventCountByDayQuery
{

    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $eventType, string $startDate, string $endDate): array
    {
        $startDateTime = new \DateTime($startDate, new \DateTimeZone('UTC'));
        $endDateTime = new \DateTime($endDate, new \DateTimeZone('UTC'));

        $sqlQuery = <<<SQL
SELECT app.code, audit.event_date, audit.event_count
FROM akeneo_app app
LEFT JOIN akeneo_app_audit audit ON audit.app_code = app.code 
AND audit.event_date BETWEEN :start_date AND :end_date
AND audit.event_type = :event_type
GROUP BY app.code, audit.event_date, audit.event_count
ORDER BY audit.event_date
SQL;
        $sqlParams = [
            'start_date' => $startDateTime->format('Y-m-d'),
            'end_date' => $endDateTime->format('Y-m-d'),
            'event_type' => $eventType,
        ];

        $result = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();

        $eventCountsDataPerApp = $this->fillMissingDates(
            $startDateTime,
            $endDateTime,
            $this->normalizeEventCountsDataPerApp($result)
        );

        return $this->hydrateWeeklyEventCountsPerApp($eventCountsDataPerApp);
    }

    private function normalizeEventCountsDataPerApp(array $dataRows): array
    {
        return array_reduce(
            $dataRows,
            function (array $data, array $row) {
                if (!isset($data[$row['code']])) {
                    $data[$row['code']] = [];
                }
                if (null !== $row['event_date']) {
                    $data[$row['code']][$row['event_date']] = (int)$row['event_count'];
                }

                return $data;
            },
            []
        );
    }

    private function fillMissingDates(
        \DateTime $start,
        \DateTime $end,
        array $eventCountsDataPerApp
    ): array {
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end->modify('+1 day')
        );

        $days = [];
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }

        foreach ($eventCountsDataPerApp as $appCode => $eventCounts) {
            foreach ($days as $day) {
                if (!isset($eventCounts[$day])) {
                    $eventCountsDataPerApp[$appCode][$day] = 0;
                }
            }
        }

        return $eventCountsDataPerApp;
    }

    private function hydrateWeeklyEventCountsPerApp(array $eventCountsDataPerApp): array
    {
        $weeklyEventCountsPerApp = [];

        foreach ($eventCountsDataPerApp as $appCode => $eventCounts) {
            $weeklyEventCounts = new WeeklyEventCounts($appCode);

            foreach ($eventCounts as $eventDate => $eventCount) {
                $weeklyEventCounts->addDailyEventCount(
                    new DailyEventCount(
                        $eventCount,
                        new \DateTimeImmutable($eventDate, new \DateTimeZone('UTC'))
                    )
                );
            }

            $weeklyEventCountsPerApp[] = $weeklyEventCounts;
        }

        return $weeklyEventCountsPerApp;
    }
}
