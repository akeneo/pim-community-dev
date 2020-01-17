<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures;

use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuditLoader
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function insertData(
        string $connectionCode,
        \DateTimeInterface $eventDate,
        int $eventCount,
        string $eventType
    ) {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit (connection_code, event_date, event_count, event_type)
VALUES (:connection_code, :event_date, :event_count, :event_type)
SQL;
        $this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'connection_code' => $connectionCode,
                'event_date' => $eventDate->format('Y-m-d'),
                'event_count' => $eventCount,
                'event_type' => $eventType
            ]
        );
    }
}
