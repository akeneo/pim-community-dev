<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAppConfirmationQuery implements GetAppConfirmationQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $marketplaceAppId): ?AppConfirmation
    {
        $query = <<<SQL
SELECT akeneo_connectivity_connected_app.id as app_id,
       akeneo_connectivity_connection.user_id as user_id,
       akeneo_connectivity_connected_app.user_group_name as user_group,
       pim_api_client.id as fos_client_id
FROM pim_api_client
JOIN akeneo_connectivity_connection on pim_api_client.id = akeneo_connectivity_connection.client_id
JOIN akeneo_connectivity_connected_app on akeneo_connectivity_connection.code = akeneo_connectivity_connected_app.connection_code
WHERE pim_api_client.marketplace_public_app_id = :marketplace_public_app_id
SQL;

        $rows = $this->connection->fetchAllAssociative($query, [
            'marketplace_public_app_id' => $marketplaceAppId,
        ]);

        if (\count($rows) > 1) {
            throw new \LogicException('There should be only one connected app by marketplace id');
        }

        if ($rows === []) {
            return null;
        }

        $row = $rows[0];

        return AppConfirmation::create(
            $row['app_id'],
            (int) $row['user_id'],
            $row['user_group'],
            (int) $row['fos_client_id'],
        );
    }
}
