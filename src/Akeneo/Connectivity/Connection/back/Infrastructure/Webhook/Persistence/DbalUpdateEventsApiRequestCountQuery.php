<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateEventsApiRequestCountQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalUpdateEventsApiRequestCountQuery implements UpdateEventsApiRequestCountQueryInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    /**
     * Update the number of Events API requests for the current hour & minute.
     * `$dateTime` minute is used to determine the minute to update and to set the last `updated` date.
     * If the previous `updated` date is not from the current hour, then the count is reinitialized with the new count.
     */
    public function execute(\DateTimeImmutable $dateTime, int $eventCount): int
    {
        $upsertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_events_api_request_count (event_minute, event_count, updated)
VALUES(:event_minute, :event_count, :updated)
ON DUPLICATE KEY UPDATE
    event_count = IF(DATE_FORMAT(updated, "%Y%m%d%H") = DATE_FORMAT(:updated, "%Y%m%d%H"), event_count + :event_count, :event_count),
    updated = :updated
SQL;

        return $this->dbalConnection->executeStatement(
            $upsertQuery,
            [
                'event_minute' => (int) $dateTime->format('i'),
                'event_count' => $eventCount,
                'updated' => $dateTime,
            ],
            [
                'updated' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }
}
