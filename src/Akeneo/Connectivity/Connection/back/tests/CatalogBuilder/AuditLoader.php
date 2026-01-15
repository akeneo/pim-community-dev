<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\BulkInsertEventCountsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditLoader
{
    public function __construct(
        private DbalConnection $dbalConnection,
        private BulkInsertEventCountsQueryInterface $bulkInsertEventCountsQuery,
    ) {
    }

    public function insert(
        HourlyEventCount $hourlyEventCount,
        ?\DateTimeInterface $updated = null
    ): void {
        $this->bulkInsertEventCountsQuery->execute([
            $hourlyEventCount
        ]);

        if (null !== $updated) {
            $this->setUpdated(
                $hourlyEventCount->connectionCode(),
                $hourlyEventCount->hourlyInterval(),
                $hourlyEventCount->eventType(),
                $updated
            );
        }
    }

    private function setUpdated(
        string $connectionCode,
        HourlyInterval $hourlyInterval,
        string $eventType,
        \DateTimeInterface $updated
    ): void {
        $this->dbalConnection->update(
            'akeneo_connectivity_connection_audit_product',
            [
                'updated' => $updated
            ],
            [
                'connection_code' => $connectionCode,
                'event_datetime' => $hourlyInterval->fromDateTime(),
                'event_type' => $eventType,
            ],
            [
                'event_datetime' => Types::DATETIME_IMMUTABLE,
                'updated' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }
}
