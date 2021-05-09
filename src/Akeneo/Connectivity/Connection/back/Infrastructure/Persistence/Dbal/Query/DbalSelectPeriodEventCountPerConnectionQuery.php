<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Traits\PeriodEventCountTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodEventCountPerConnectionQuery implements SelectPeriodEventCountPerConnectionQuery
{
    use PeriodEventCountTrait;

    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * @return PeriodEventCount[]
     */
    public function execute(
        string $eventType,
        DateTimePeriod $period
    ): array {
        $connectionCodes = $this->getConnectionCodes($eventType);

        $perConnection = $this->getPeriodEventCountPerConnection($eventType, $period, $connectionCodes);
        $forAllConnections = $this->getPeriodEventCountForAllConnections($eventType, $period, $connectionCodes);

        return $this->createPeriodEventCountPerConnection(
            $period,
            $connectionCodes,
            array_merge($perConnection, $forAllConnections),
        );
    }

    /**
     * @return string[]
     */
    private function getConnectionCodes(string $eventType): array
    {
        $sql = <<<SQL
SELECT code from akeneo_connectivity_connection
WHERE flow_type = :flow_type AND auditable = 1
SQL;

        $resultStmt = $this->dbalConnection->executeQuery(
            $sql,
            [
                'flow_type' => $this->getFlowTypeForEventType($eventType),
            ],
        );

        $connectionCodes = [];
        while ($code = $resultStmt->fetchColumn()) {
            $connectionCodes[] = $code;
        }

        return $connectionCodes;
    }

    /**
     * @param string[] $connectionCodes
     * @return mixed[]
     */
    private function getPeriodEventCountPerConnection(
        string $eventType,
        DateTimePeriod $period,
        array $connectionCodes
    ): array {
        $sql = <<<SQL
SELECT conn.code as connection_code, audit.event_datetime, audit.event_count
FROM akeneo_connectivity_connection conn
LEFT JOIN akeneo_connectivity_connection_audit_product audit ON audit.connection_code = conn.code
    AND audit.event_datetime >= :from_datetime AND audit.event_datetime < :up_to_datetime
    AND audit.event_type = :event_type
WHERE connection_code IN (:connection_codes)
ORDER BY conn.code, audit.event_datetime
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'event_type' => $eventType,
                'from_datetime' => $period->start(),
                'up_to_datetime' => $period->end(),
                'connection_codes' => $connectionCodes,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
                'connection_codes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll();
    }

    /**
     * @param string[] $connectionCodes
     * @return mixed[]
     */
    private function getPeriodEventCountForAllConnections(
        string $eventType,
        DateTimePeriod $period,
        array $connectionCodes
    ): array {
        $sql = <<<SQL
SELECT :all as connection_code, event_datetime, SUM(event_count) as event_count
FROM akeneo_connectivity_connection_audit_product
WHERE connection_code IN (:connection_codes)
AND event_datetime >= :from_datetime AND event_datetime < :up_to_datetime
AND event_type = :event_type
GROUP BY event_datetime
ORDER BY event_datetime
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'all' => AllConnectionCode::CODE,
                'event_type' => $eventType,
                'from_datetime' => $period->start(),
                'up_to_datetime' => $period->end(),
                'connection_codes' => $connectionCodes,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
                'connection_codes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll();
    }

    private function getFlowTypeForEventType(string $eventType): string
    {
        $flowType = '';

        switch ($eventType) {
            case EventTypes::PRODUCT_CREATED:
            case EventTypes::PRODUCT_UPDATED:
                $flowType = FlowType::DATA_SOURCE;
                break;
            case EventTypes::PRODUCT_READ:
                $flowType = FlowType::DATA_DESTINATION;
                break;
            default:
                throw new \LogicException(
                    sprintf('$eventType must be "product_created", "product_updated" or "product_read", but "%s" given.', $eventType)
                );
        }

        return $flowType;
    }
}
