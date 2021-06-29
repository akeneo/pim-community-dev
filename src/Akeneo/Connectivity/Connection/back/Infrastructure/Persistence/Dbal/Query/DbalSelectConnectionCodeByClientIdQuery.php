<?php
declare(strict_types=1);


namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Doctrine\DBAL\Connection;

class DbalSelectConnectionCodeByClientIdQuery implements SelectConnectionCodeByClientIdQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
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
            ->fetchColumn();

        if (false === $connectionCode) {
            return null;
        }

        return (string) $connectionCode;
    }
}
