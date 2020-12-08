<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiRequestCountRepository;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalEventsApiRequestCountRepository implements EventsApiRequestCountRepository
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function upsert(\DateTimeImmutable $dateTime, int $eventCount): int
    {
        $upsertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_events_api_request_count (event_minute, event_count, updated)
VALUES(:event_minute, :event_count, :updated)
ON DUPLICATE KEY UPDATE event_count = event_count + :event_count, updated = :updated
SQL;

        $this->dbalConnection->executeUpdate(
            $upsertQuery,
            [
                'event_minute' => (int)$dateTime->format('i'),
                'event_count' => $eventCount,
                'updated' => $dateTime,
            ],
            [
                'updated' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }
}
