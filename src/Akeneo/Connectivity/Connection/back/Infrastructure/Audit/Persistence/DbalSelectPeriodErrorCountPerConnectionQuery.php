<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\Traits\PeriodEventCountTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodErrorCountPerConnectionQuery implements SelectPeriodErrorCountPerConnectionQueryInterface
{
    use PeriodEventCountTrait;

    public function __construct(private Connection $dbalConnection)
    {
    }

    /**
     * @return PeriodEventCount[]
     */
    public function execute(DateTimePeriod $period): array
    {
        $connectionCodes = $this->getConnectionCodes();

        $perConnection = $this->getPeriodErrorCountPerConnection($period, $connectionCodes);
        $forAllConnections = $this->getPeriodErrorCountForAllConnections($period, $connectionCodes);

        return $this->createPeriodEventCountPerConnection(
            $period,
            $connectionCodes,
            \array_merge($perConnection, $forAllConnections)
        );
    }

    /**
     * @return string[]
     */
    private function getConnectionCodes(): array
    {
        $sql = <<<SQL
SELECT code from akeneo_connectivity_connection
WHERE flow_type = :flow_type AND auditable = 1
SQL;

        $resultStmt = $this->dbalConnection->executeQuery(
            $sql,
            [
                'flow_type' => FlowType::DATA_SOURCE,
            ],
        );

        $connectionCodes = [];
        while ($code = $resultStmt->fetchOne()) {
            $connectionCodes[] = $code;
        }

        return $connectionCodes;
    }

    /**
     * Return the count of errors per hour for each connection.
     *
     * @param string[] $connectionCodes
     *
     * @return array<array{connection_code: string, event_datetime: string, event_count: string}>
     */
    private function getPeriodErrorCountPerConnection(DateTimePeriod $period, array $connectionCodes): array
    {
        $sql = <<<SQL
SELECT connection_code, error_datetime as event_datetime, SUM(error_count) as event_count
FROM akeneo_connectivity_connection_audit_error
WHERE connection_code IN (:connection_codes)
AND error_datetime >= :from_datetime AND error_datetime < :up_to_datetime
GROUP BY connection_code, event_datetime
ORDER BY connection_code ASC, error_datetime ASC
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'connection_codes' => $connectionCodes,
                'from_datetime' => $period->start(),
                'up_to_datetime' => $period->end(),
            ],
            [
                'connection_codes' => Connection::PARAM_STR_ARRAY,
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAllAssociative();
    }

    /**
     * Return the count of errors per hour for all the connections.
     *
     * @param string[] $connectionCodes
     *
     * @return array<array{connection_code: string, event_datetime: string, event_count: string}>
     */
    private function getPeriodErrorCountForAllConnections(DateTimePeriod $period, array $connectionCodes): array
    {
        $sql = <<<SQL
SELECT :all as connection_code, error_datetime as event_datetime, SUM(error_count) as event_count
FROM akeneo_connectivity_connection_audit_error
WHERE connection_code IN (:connection_codes)
AND error_datetime >= :from_datetime AND error_datetime < :up_to_datetime
GROUP BY error_datetime
ORDER BY error_datetime ASC
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'all' => AllConnectionCode::CODE,
                'connection_codes' => $connectionCodes,
                'from_datetime' => $period->start(),
                'up_to_datetime' => $period->end(),
            ],
            [
                'connection_codes' => Connection::PARAM_STR_ARRAY,
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAllAssociative();
    }
}
