<?php

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\UpdateCustomAppSecretQueryInterface;
use Doctrine\DBAL\Connection;

class UpdateCustomAppSecretQuery implements UpdateCustomAppSecretQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function execute($clientId, $clientSecret): void
    {
        $query = <<<SQL
        UPDATE akeneo_connectivity_test_app 
        SET client_secret = :clientSecret
        WHERE client_id = :clientId
        SQL;

        $this->connection->executeQuery($query, [
            'clientSecret' => $clientSecret,
            'clientId' => $clientId,
        ]);
    }
}
