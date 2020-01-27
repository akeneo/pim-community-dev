<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsEventCountByDayQuery implements SelectConnectionsEventCountByDayQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $eventType, string $startDate, string $endDate): array
    {
        $eventCountsDataPerConnection = $this->getPerConnection($startDate, $endDate, $eventType);
        $eventCountsDataForAllConnections = $this->getForAllConnections($startDate, $endDate, $eventType);
        $eventCounts = array_merge($eventCountsDataPerConnection, $eventCountsDataForAllConnections);

        $weeklyEventCountsPerConnection = [];
        foreach ($eventCounts as $code => $eventCount) {
            $weeklyEventCountsPerConnection[$code] = new WeeklyEventCounts($code, $startDate, $endDate, $eventCount);
        }

        return $weeklyEventCountsPerConnection;
    }

    private function getForAllConnections(
        string $startDate,
        string $endDate,
        string $eventType
    ): array {
        $startDateTime = new \DateTime($startDate, new \DateTimeZone('UTC'));
        $endDateTime = new \DateTime($endDate, new \DateTimeZone('UTC'));

        $sqlQuery = <<<SQL
SELECT connection_code as code, event_date, event_count
FROM akeneo_connectivity_connection_audit
WHERE connection_code = '<all>'
AND event_date BETWEEN :start_date AND :end_date
AND event_type = :event_type
ORDER BY event_date
SQL;
        $sqlParams = [
            'start_date' => $startDateTime->format('Y-m-d'),
            'end_date' => $endDateTime->format('Y-m-d'),
            'event_type' => $eventType,
        ];

        $result = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();
        if (empty($result)) {
            $result = [['code' => '<all>', 'event_date' => null, 'event_count' => null]];
        }

        return $this->normalizeEventCountsData($result);
    }

    private function getPerConnection(
        string $startDate,
        string $endDate,
        string $eventType
    ): array {
        $startDateTime = new \DateTime($startDate, new \DateTimeZone('UTC'));
        $endDateTime = new \DateTime($endDate, new \DateTimeZone('UTC'));

        $sqlQuery = <<<SQL
SELECT conn.code, audit.event_date, audit.event_count
FROM akeneo_connectivity_connection conn
LEFT JOIN akeneo_connectivity_connection_audit audit ON audit.connection_code = conn.code
AND audit.event_date BETWEEN :start_date AND :end_date
AND audit.event_type = :event_type
ORDER BY audit.event_date
SQL;
        $sqlParams = [
            'start_date' => $startDateTime->format('Y-m-d'),
            'end_date' => $endDateTime->format('Y-m-d'),
            'event_type' => $eventType,
        ];

        $result = $this->dbalConnection->executeQuery($sqlQuery, $sqlParams)->fetchAll();

        return $this->normalizeEventCountsData($result);
    }

    /**
     * Return normalized data
     * [
     *  'bynder' => ['2020-01-02' => 12, '2020-01-03' => 10]
     *  'magento' => ['2020-01-04' => 2]
     * ]
     */
    private function normalizeEventCountsData(array $dataRows): array
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
}
