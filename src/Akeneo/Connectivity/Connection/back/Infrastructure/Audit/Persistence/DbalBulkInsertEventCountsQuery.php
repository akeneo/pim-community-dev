<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\BulkInsertEventCountsQueryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalBulkInsertEventCountsQuery implements BulkInsertEventCountsQueryInterface
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    public function execute(array $hourlyEventCounts): void
    {
        foreach ($hourlyEventCounts as $hourlyEventCount) {
            $this->insert($hourlyEventCount);
        }
    }

    private function insert(HourlyEventCount $hourlyEventCount): void
    {
        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product (connection_code, event_datetime, event_count, event_type, updated)
VALUES(:connection_code, :event_datetime, :event_count, :event_type, UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE event_count = :event_count, updated = UTC_TIMESTAMP()
SQL;

        $this->dbalConnection->executeStatement(
            $insertQuery,
            [
                'connection_code' => $hourlyEventCount->connectionCode(),
                'event_datetime' => $hourlyEventCount->hourlyInterval()->fromDateTime(),
                'event_count' => (int) $hourlyEventCount->eventCount(),
                'event_type' => (string) $hourlyEventCount->eventType(),
            ],
            [
                'event_datetime' => Types::DATETIME_IMMUTABLE,
                'event_count' => Types::INTEGER,
            ]
        );
    }
}
