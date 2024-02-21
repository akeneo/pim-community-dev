<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppScopesQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectedAppScopesQuery implements UpdateConnectedAppScopesQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(array $scopes, string $appId): void
    {
        $updateQuery = <<<SQL
        UPDATE akeneo_connectivity_connected_app
        SET
            scopes = :scopes,
            updated = NOW()
        WHERE id = :id
        SQL;

        $this->connection->executeQuery(
            $updateQuery,
            [
                'scopes' => $scopes,
                'id' => $appId,
            ],
            [
                'scopes' => Types::JSON,
            ]
        );
    }
}
