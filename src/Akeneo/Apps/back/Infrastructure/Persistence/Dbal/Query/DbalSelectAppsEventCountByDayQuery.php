<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Domain\Audit\Model\Read\AppEventCounts;
use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
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
SELECT app_code, JSON_OBJECTAGG(au.event_date, au.event_count) as event_count
FROM akeneo_app_audit au
WHERE event_date BETWEEN :start_date AND :end_date
AND event_type = :event_type
GROUP BY app_code
SQL;
        $sqlParams = [
            'start_date' => $startDateTime->format('Y-m-d'),
            'end_date'   => $endDateTime->format('Y-m-d'),
            'event_type' => $eventType
        ];

        $dataRows = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();

        $eventCountByApps = [];
        foreach ($dataRows as $dataRow) {
            $eventCountByApps[] = $this->hydrateRow($dataRow);
        }

        return $eventCountByApps;
    }

    private function hydrateRow(array $dataRow): AppEventCounts
    {
        $eventCountByApp = new AppEventCounts($dataRow['app_code']);
        foreach (json_decode($dataRow['event_count'], true) as $eventDate => $eventCount) {
            $eventCountByApp->addDailyEventCount(
                new DailyEventCount($eventCount, new \DateTime($eventDate, new \DateTimeZone('UTC')))
            );
        }

        return $eventCountByApp;
    }
}
