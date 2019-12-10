<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByApp;
use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByDate;
use Akeneo\Apps\Audit\Domain\Persistence\Query\SelectAppsEventCountByDateQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectAppsEventCountByDateQuery implements SelectAppsEventCountByDateQuery
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
SELECT app.code, app.label, JSON_OBJECTAGG(au.event_date, au.event_count) as event_count
FROM akeneo_app_audit au
INNER JOIN akeneo_app app ON app.code = au.app_code
WHERE event_date BETWEEN :start_date AND :end_date
AND event_type = :event_type
GROUP BY app.code, app.label
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

    private function hydrateRow(array $dataRow): EventCountByApp
    {
        $eventCountByApp = new EventCountByApp($dataRow['label']);
        foreach (json_decode($dataRow['event_count'], true) as $eventDate => $eventCount) {
            $eventCountByApp->addEventCount(
                new EventCountByDate($eventCount, new \DateTime($eventDate, new \DateTimeZone('UTC')))
            );
        }

        return $eventCountByApp;
    }
}
