<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventsApiRequestCountWithinLastHourQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectEventsApiRequestCountWithinLastHourQuery implements SelectEventsApiRequestCountWithinLastHourQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(\DateTimeImmutable $eventDateTime): array
    {
        $oneHourAgoEventDateTime = $eventDateTime->modify('-1 hour');
        $sql = <<<SQL
 SELECT updated, event_count
 FROM akeneo_connectivity_connection_events_api_request_count
 WHERE updated BETWEEN :from_datetime  AND :to_datetime
 ORDER BY updated DESC
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'from_datetime' => $oneHourAgoEventDateTime,
                'to_datetime' => $eventDateTime,
            ],
            [
                'from_datetime' => Types::DATETIME_IMMUTABLE,
                'to_datetime' => Types::DATETIME_IMMUTABLE,
            ]
        )->fetchAll();
    }
}
