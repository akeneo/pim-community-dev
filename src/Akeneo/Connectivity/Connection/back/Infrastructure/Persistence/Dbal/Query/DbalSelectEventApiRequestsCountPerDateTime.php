<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectEventApiRequestsCountPerDateTime;
use Doctrine\DBAL\Connection;

class DbalSelectEventApiRequestsCountPerDateTime implements SelectEventApiRequestsCountPerDateTime
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(\DateTimeImmutable $dateTime): int
    {
        $dateTimeFrom = $dateTime->format('Y-m-d H:i:00');
        $dateTimeTo = $dateTime->format('Y-m-d H:i:59');

        $sqlQuery = <<<SQL
 SELECT `count` AS event_count
 FROM akeneo_connectivity_connection_events_api_request_count 
 WHERE updated BETWEEN :from_datetime  AND :to_datetime
SQL;

        return (int)$this->dbalConnection->executeQuery(
            $sqlQuery,
            [
                'from_datetime' => $dateTimeFrom,
                'to_datetime' => $dateTimeTo,
            ],
        )->fetchColumn();
    }
}