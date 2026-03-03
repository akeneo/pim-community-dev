<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllConnectedAppsQueryInterface;
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
            connected_app.id,
            connected_app.name,
            connected_app.scopes,
            connected_app.connection_code,
            connected_app.logo,
            connected_app.author,
            connected_app.user_group_name,
            connected_app.categories,
            connected_app.certified,
            connected_app.partner,
            oro_user.username AS connection_username,
            IF(akeneo_connectivity_test_app.client_id IS NULL, FALSE, TRUE) AS is_custom_app,
            IF(pending.marketplace_public_app_id IS NULL, FALSE, TRUE) AS is_pending,
            connected_app.has_outdated_scopes
        FROM akeneo_connectivity_connected_app AS connected_app
        JOIN akeneo_connectivity_connection connection ON connection.code = connected_app.connection_code
        JOIN oro_user ON oro_user.id = connection.user_id
        LEFT JOIN akeneo_connectivity_test_app ON akeneo_connectivity_test_app.client_id = connected_app.id
        LEFT JOIN pending ON connected_app.id = pending.marketplace_public_app_id
        ORDER BY connected_app.name ASC
        SQL;

        $dataRows = $this->connection->executeQuery($selectSQL)->fetchAllAssociative();

        return \array_map(
            fn ($dataRow): ConnectedApp => $this->denormalizeRow($dataRow),
            $dataRows
        );
    }
}
