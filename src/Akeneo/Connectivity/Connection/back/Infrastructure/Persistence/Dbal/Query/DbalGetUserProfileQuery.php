<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Doctrine\DBAL\Connection;

class DbalGetUserProfileQuery implements GetUserProfileQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $username): ?string
    {
        $sql = <<<SQL
SELECT profile
FROM oro_user
WHERE username = :username
SQL;

        return $this->connection->fetchColumn($sql, ['username' => $username]);
    }
}
