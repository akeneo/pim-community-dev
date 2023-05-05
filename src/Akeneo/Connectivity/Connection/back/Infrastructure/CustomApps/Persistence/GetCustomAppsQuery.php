<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppsQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsQuery implements GetCustomAppsQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(int $userId): array
    {
        $query = <<<SQL
        SELECT 
            app.client_id,
            app.name,
            app.activate_url,
            app.callback_url
        FROM akeneo_connectivity_test_app AS app
        WHERE app.user_id = :user_id
        ORDER BY app.client_id
        SQL;

        return $this->connection->fetchAllAssociative(
            $query,
            [
                'user_id' => $userId,
            ],
            [
                'user_id' => Types::INTEGER,
            ]
        );
    }
}
