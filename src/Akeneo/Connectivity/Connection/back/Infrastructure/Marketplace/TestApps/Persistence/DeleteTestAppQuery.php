<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\DeleteTestAppQueryInterface;
use Doctrine\DBAL\Connection;

class DeleteTestAppQuery implements DeleteTestAppQueryInterface
{
    public function __construct(private Connection $connection)
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
