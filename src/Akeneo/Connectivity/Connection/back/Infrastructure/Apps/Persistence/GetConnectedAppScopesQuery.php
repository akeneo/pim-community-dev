<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectedAppScopesQuery implements GetConnectedAppScopesQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $appId): array
    {
        $query = <<<SQL
            SELECT scopes FROM akeneo_connectivity_connected_app
            WHERE id = :id
            SQL;

        $scopes = $this->connection->fetchOne($query, [
            'id' => $appId,
        ]);

        return empty($scopes) ? [] : \json_decode($scopes, null, 512, JSON_THROW_ON_ERROR);
    }
}
