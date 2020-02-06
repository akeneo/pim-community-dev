<?php
declare(strict_types=1);


namespace Akeneo\Connectivity\Connection\Infrastructure\WrongCredentialsConnection\Persistence\Dbal;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Doctrine\DBAL\Connection;

class DbalSelectConnectionCodeByClientIdQuery implements SelectConnectionCodeByClientIdQuery
{
    /** @var Connection */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $clientId): string
    {
        $sqlQuery = <<<SQL
SELECT connection.code
FROM akeneo_connectivity_connection connection
WHERE connection.client_id = :client_id
SQL;

        return (string) $this->dbalConnection->executeQuery($sqlQuery, ['client_id' => $clientId])->fetchColumn();
    }
}
