<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures;

use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventApiRequestsCountLoader
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function createEventApiRequestsCount(\DateTimeImmutable $dateTime, int $count)
    {
        $query = <<<SQL
INSERT INTO akeneo_connectivity_connection_events_api_request_count(event_minute, event_count, updated) 
VALUES (:event_minute,:event_count,:updated)
SQL;

        $this->dbalConnection->executeUpdate(
            $query,
            [
                'event_minute' => (int)$dateTime->format('i'),
                'event_count' => $count,
                'updated' => $dateTime,
            ],
            [
                'updated' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }
}
