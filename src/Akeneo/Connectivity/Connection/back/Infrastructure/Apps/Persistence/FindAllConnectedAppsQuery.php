<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllConnectedAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DenormalizeConnectedAppTrait;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindAllConnectedAppsQuery implements FindAllConnectedAppsQueryInterface
{
    use DenormalizeConnectedAppTrait;

    public function __construct(private Connection $connection)
    {
    }

    public function execute(): array
    {
        $selectSQL = <<<SQL
        WITH pending (marketplace_public_app_id) AS (
            SELECT marketplace_public_app_id
            FROM pim_api_client
            LEFT JOIN pim_api_access_token as access_token ON pim_api_client.id = access_token.client
            LEFT JOIN pim_api_auth_code as auth_code ON pim_api_client.id = auth_code.client_id
            WHERE access_token.token IS NULL AND auth_code.token IS NOT NULL
        )
        SELECT
            id,
            connected_app.name,
            scopes,
            connection_code,
            logo,
            author,
            user_group_name,
            categories,
            certified,
            partner,
            IF(akeneo_connectivity_test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app,
            IF(pending.marketplace_public_app_id IS NULL, FALSE, TRUE) AS is_pending
        FROM akeneo_connectivity_connected_app AS connected_app
        LEFT JOIN akeneo_connectivity_test_app ON akeneo_connectivity_test_app.client_id = connected_app.id
        LEFT JOIN pending ON connected_app.id = pending.marketplace_public_app_id
        ORDER BY name ASC
        SQL;

        $dataRows = $this->connection->executeQuery($selectSQL)->fetchAll();

        $connectedApps = \array_map(
            fn ($dataRow) => $this->denormalizeRow($dataRow),
            $dataRows
        );

        return $connectedApps;
    }
}
