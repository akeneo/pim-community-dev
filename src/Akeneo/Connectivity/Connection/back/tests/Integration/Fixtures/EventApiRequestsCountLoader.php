<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures;


use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

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
INSERT INTO akeneo_connectivity_connection_events_api_request_count
(`minute`,`count`, updated) 
VALUES (:minute,:count,:updated)
SQL;

        $res = $this->dbalConnection->executeUpdate(
            $query,
            [
                'minute' => (int)$dateTime->format('i'),
                'count' => $count,
                'updated' => $dateTime,
            ],
            [
                'updated' => Types::DATE_IMMUTABLE,
            ]
        );

        dump($res);
    }
}
