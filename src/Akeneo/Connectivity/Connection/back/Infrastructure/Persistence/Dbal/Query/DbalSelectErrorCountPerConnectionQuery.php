<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectErrorCountPerConnectionQuery implements SelectErrorCountPerConnectionQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(
        ErrorType $errorType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ): ErrorCountPerConnection {
        $sqlQuery = <<<SQL
SELECT connection_code, sum(error_count) as error_count
FROM akeneo_connectivity_connection_audit_error
WHERE error_type = :error_type
AND error_datetime >= :from_datetime AND error_datetime < :up_to_datetime
GROUP BY connection_code
SQL;

        $dataRows = $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'error_type' => $errorType,
                'from_datetime' => $fromDateTime,
                'up_to_datetime' => $upToDateTime,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'up_to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAll();

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
