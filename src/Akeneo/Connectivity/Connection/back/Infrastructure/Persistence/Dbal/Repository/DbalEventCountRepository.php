<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\DailyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepository;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalEventCountRepository implements EventCountRepository
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function bulkInsert(array $dailyEventCounts): void
    {
        $this->dbalConnection->beginTransaction();

        foreach ($dailyEventCounts as $dailyEventCount) {
            $this->insert($dailyEventCount);
        }
        $this->dbalConnection->commit();
    }

    private function insert(DailyEventCount $dailyEventCount): void
    {
        // TODO: Deal with duplication
        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit (connection_code, event_date, event_count, event_type)
VALUES(:connection_code, :event_date, :event_count, :event_type)
ON DUPLICATE KEY UPDATE event_count = :event_count
SQL;
        $stmt = $this->dbalConnection->prepare($insertQuery);
        $stmt->execute([
            'connection_code' => $dailyEventCount->connectionCode(),
            'event_date' => $dailyEventCount->eventDate(),
            'event_count' => (int) $dailyEventCount->eventCount(),
            'event_type' => (string) $dailyEventCount->eventType(),
        ]);
    }
}
