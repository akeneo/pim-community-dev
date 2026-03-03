<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteConnectedAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteConnectedAppQuery implements DeleteConnectedAppQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $appId): void
    {
        $query = <<<SQL
DELETE FROM akeneo_connectivity_connected_app
WHERE id = :id
SQL;

        $this->connection->executeQuery($query, [
            'id' => $appId,
        ]);
    }
}
