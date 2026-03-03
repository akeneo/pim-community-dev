<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Persistence;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQueryInterface;
use Doctrine\DBAL\Connection;

class DbalSelectConnectionCodeByClientIdQuery implements SelectConnectionCodeByClientIdQueryInterface
{
    public function __construct(private Connection $dbalConnection)
    {
    }

    public function execute(string $clientId): ?string
    {
        $sqlQuery = <<<SQL
SELECT code
FROM akeneo_connectivity_connection
WHERE client_id = :client_id
SQL;

        $connectionCode = $this->dbalConnection
            ->executeQuery($sqlQuery, ['client_id' => $clientId])
            ->fetchOne();

        if (false === $connectionCode) {
            return null;
        }

        return (string) $connectionCode;
    }
}
