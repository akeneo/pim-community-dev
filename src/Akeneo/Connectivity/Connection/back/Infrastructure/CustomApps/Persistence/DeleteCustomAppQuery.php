<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\DeleteCustomAppQueryInterface;
use Doctrine\DBAL\Connection;

class DeleteCustomAppQuery implements DeleteCustomAppQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(string $clientId): void
    {
        $query = <<<SQL
        DELETE FROM akeneo_connectivity_test_app
        WHERE client_id = :client_id
        SQL;

        $this->connection->executeQuery($query, [
            'client_id' => $clientId,
        ]);
    }
}
