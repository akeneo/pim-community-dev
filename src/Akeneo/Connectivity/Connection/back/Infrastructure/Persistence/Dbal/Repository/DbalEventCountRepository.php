<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepository;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalEventCountRepository implements EventCountRepository
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function bulkInsert(array $hourlyEventCounts): void
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

        $this->dbalConnection->executeUpdate(
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

    public function upsert(HourlyEventCount $hourlyEventCount): void
    {
        $upsertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_product (connection_code, event_datetime, event_count, event_type, updated)
VALUES(:connection_code, :event_datetime, :event_count, :event_type, UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE event_count = event_count + :event_count, updated = UTC_TIMESTAMP()
SQL;

        $this->dbalConnection->executeUpdate(
            $upsertQuery,
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
