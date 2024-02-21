<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectErrorCountPerConnectionQuery implements SelectErrorCountPerConnectionQueryInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    public function execute(
        ErrorType $errorType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): ErrorCountPerConnection {
        $sqlQuery = <<<SQL
SELECT conn.code as connection_code, IFNULL(SUM(error.error_count), 0) as error_count

FROM akeneo_connectivity_connection AS conn
LEFT JOIN akeneo_connectivity_connection_audit_error AS error
    ON conn.code = error.connection_code
    AND error.error_type = :error_type
    AND error_datetime >= :from_datetime AND error_datetime < :up_to_datetime
	
WHERE conn.auditable = 1
AND conn.flow_type = :flow_type

GROUP BY conn.code
SQL;

        $dataRows = $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'error_type' => $errorType,
                'from_datetime' => $fromDateTime,
                'up_to_datetime' => $upToDateTime,
                'flow_type' => FlowType::DATA_SOURCE
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAllAssociative();

        $errorCounts = [];
        foreach ($dataRows as $dataRow) {
            $errorCounts[] = new ErrorCount(
                $dataRow['connection_code'],
                (int) $dataRow['error_count'],
            );
        }

        return new ErrorCountPerConnection($errorCounts);
    }
}
