<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveConnectedAppOutdatedScopesFlagQuery implements SaveConnectedAppOutdatedScopesFlagQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $connectedAppId, bool $hasOutdatedScopes): void
    {
        $query = <<<SQL
        UPDATE akeneo_connectivity_connected_app
        SET
            has_outdated_scopes = :has_outdated_scopes,
            updated = NOW()
        WHERE id = :id
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $connectedAppId,
                'has_outdated_scopes' => $hasOutdatedScopes,
            ],
            [
                'has_outdated_scopes' => Types::BOOLEAN,
            ]
        );
    }
}
