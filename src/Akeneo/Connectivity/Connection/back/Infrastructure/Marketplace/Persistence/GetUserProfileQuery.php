<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Doctrine\DBAL\Connection;

class GetUserProfileQuery implements GetUserProfileQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $username): ?string
    {
        $sql = <<<SQL
SELECT profile
FROM oro_user
WHERE username = :username
SQL;

        return $this->connection->fetchOne($sql, ['username' => $username]);
    }
}
