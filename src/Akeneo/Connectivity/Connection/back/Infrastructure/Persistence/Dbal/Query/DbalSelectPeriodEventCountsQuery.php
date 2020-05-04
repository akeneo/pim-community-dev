<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountsQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodEventCountsQuery implements SelectPeriodEventCountsQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): array {
        $hourlyEventCountsPerConnectionData = $this->getHourlyEventCountsPerConnection(
            $eventType,
            $fromDateTime,
            $upToDateTime
        );

        $hourlyEventCountsForAllConnectionsData = $this->getHourlyEventCountsForAllConnections(
            $eventType,
            $fromDateTime,
            $upToDateTime
        );

        return $this->createPeriodEventCounts(
            array_merge($hourlyEventCountsPerConnectionData, $hourlyEventCountsForAllConnectionsData),
            $fromDateTime,
            $upToDateTime
        );
    }

    private function getHourlyEventCountsPerConnection(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): array {
        $sql = <<<SQL
SELECT conn.code as connection_code, audit.event_datetime, audit.event_count
FROM akeneo_connectivity_connection conn
LEFT JOIN akeneo_connectivity_connection_audit_product audit ON audit.connection_code = conn.code
AND audit.event_datetime >= :from_datetime AND audit.event_datetime < :up_to_datetime
AND audit.event_type = :event_type
ORDER BY conn.code, audit.event_datetime
SQL;

        $hourlyEventCountsData = $this->dbalConnection->executeQuery(
            $sql,
            [
                'from_datetime' => $fromDateTime,
                'up_to_datetime' => $upToDateTime,
                'event_type' => $eventType,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAll();

        return $hourlyEventCountsData;
    }

    private function getHourlyEventCountsForAllConnections(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): array {
        $sql = <<<SQL
SELECT connection_code, event_datetime, event_count
FROM akeneo_connectivity_connection_audit_product
WHERE connection_code = :connection_code
AND event_datetime >= :from_datetime AND event_datetime < :up_to_datetime
AND event_type = :event_type
ORDER BY event_datetime
SQL;
        $hourlyEventCountsData = $this->dbalConnection->executeQuery(
            $sql,
            [
                'connection_code' => AllConnectionCode::CODE,
                'from_datetime' => $fromDateTime,
                'up_to_datetime' => $upToDateTime,
                'event_type' => $eventType,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAll();

        if (empty($hourlyEventCountsData)) {
            $hourlyEventCountsData[] = [
                'connection_code' => AllConnectionCode::CODE,
                'event_datetime' => null,
                'event_count' => null
            ];
        }

        return $hourlyEventCountsData;
    }

    /**
     * @param array $hourlyEventCountsData = [
     *      ['connection_code => $connectionCode, 'event_datetime' => '2020-01-01 00:00:00', 'event_count' => 3],
     * ]
     *
     * @return PeriodEventCount[]
     */
    private function createPeriodEventCounts(
        array $hourlyEventCountsData,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): array {
        $format = $this->dbalConnection->getDatabasePlatform()->getDateTimeFormatString();

        $hourlyEventCountsPerConnection = array_reduce(
            $hourlyEventCountsData,
            function (array $data, array $row) use ($format) {
                $connectionCode = $row['connection_code'];

                if (false === isset($data[$connectionCode])) {
                    $data[$connectionCode] = [];
                }

                if (null !== $row['event_datetime'] && null !== $row['event_count']) {
                    $data[$connectionCode][] = new HourlyEventCount(
                        \DateTimeImmutable::createFromFormat(
                            $format,
                            $row['event_datetime'],
                            new \DateTimeZone('UTC')
                        ),
                        (int) $row['event_count']
                    );
                }

                return $data;
            },
            []
        );

        $periodEventCounts = [];
        foreach ($hourlyEventCountsPerConnection as $connectionCode => $hourlyEventCounts) {
            $periodEventCounts[] = new PeriodEventCount(
                $connectionCode,
                $fromDateTime,
                $upToDateTime,
                $hourlyEventCounts
            );
        }

        return $periodEventCounts;
    }
}
