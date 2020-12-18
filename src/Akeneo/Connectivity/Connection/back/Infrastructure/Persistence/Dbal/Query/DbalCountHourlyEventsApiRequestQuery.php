<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\CountHourlyEventsApiRequestQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalCountHourlyEventsApiRequestQuery implements CountHourlyEventsApiRequestQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(\DateTimeImmutable $eventDateTime): int
    {
        $oneHourAgoEventDateTime = $eventDateTime->modify('-1 hour');
        $sql = <<<SQL
 SELECT sum(event_count)	
 FROM akeneo_connectivity_connection_events_api_request_count 	
 WHERE updated BETWEEN :from_datetime  AND :to_datetime	
SQL;

        return (int)$this->dbalConnection->executeQuery(
            $sql,
            [
                'from_datetime' => $oneHourAgoEventDateTime,
                'to_datetime' => $eventDateTime,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchColumn();
    }
}
