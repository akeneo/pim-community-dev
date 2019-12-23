<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Apps\Domain\Audit\Model\Write\DailyEventCount;
use Akeneo\Apps\Domain\Audit\Persistence\Repository\EventCountRepository;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalEventCountRepository implements EventCountRepository
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
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
INSERT INTO akeneo_app_audit (app_code, event_date, event_count, event_type)
VALUES(:app_code, :event_date, :event_count, :event_type)
SQL;
        $stmt = $this->dbalConnection->prepare($insertQuery);
        $stmt->execute([
            'app_code' => $dailyEventCount->appCode(),
            'event_date' => $dailyEventCount->eventDate(),
            'event_count' => (int) $dailyEventCount->eventCount(),
            'event_type' => (string) $dailyEventCount->eventType(),
        ]);
    }
}
