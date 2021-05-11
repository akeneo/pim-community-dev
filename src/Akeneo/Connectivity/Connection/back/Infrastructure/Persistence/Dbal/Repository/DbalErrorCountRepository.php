<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\ErrorCountRepository;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalErrorCountRepository implements ErrorCountRepository
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function upsert(HourlyErrorCount $hourlyErrorCount): void
    {
        $upsertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_error (connection_code, error_datetime, error_count, error_type)
VALUES(:connection_code, :error_datetime, :error_count, :error_type)
ON DUPLICATE KEY UPDATE error_count = error_count + :error_count
SQL;

        $this->dbalConnection->executeUpdate(
            $upsertQuery,
            [
                'connection_code' => (string) $hourlyErrorCount->connectionCode(),
                'error_datetime' => $hourlyErrorCount->hourlyInterval()->fromDateTime(),
                'error_count' => $hourlyErrorCount->errorCount(),
                'error_type' => (string) $hourlyErrorCount->errorType(),
            ],
            [
                'error_datetime' => Types::DATETIME_IMMUTABLE,
                'error_count' => Types::INTEGER,
            ]
        );
    }
}
