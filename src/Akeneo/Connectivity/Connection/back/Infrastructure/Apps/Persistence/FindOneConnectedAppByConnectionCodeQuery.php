<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DenormalizeConnectedAppTrait;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneConnectedAppByConnectionCodeQuery implements FindOneConnectedAppByConnectionCodeQueryInterface
{
    use DenormalizeConnectedAppTrait;

    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $connectionCode): ?ConnectedApp
    {
        $selectQuery = <<<SQL
        SELECT
            connected_app.id,
            connected_app.name,
            connected_app.logo,
            connected_app.author,
            connected_app.partner,
            connected_app.categories,
            connected_app.scopes,
            connected_app.certified,
            connected_app.connection_code,
            connected_app.user_group_name,
            oro_user.username AS connection_username,
            IF(test_app.client_id IS NULL, FALSE, TRUE) AS is_custom_app,
            IF(access_token.token IS NULL AND auth_code.token IS NOT NULL, TRUE, FALSE) AS is_pending,
            connected_app.has_outdated_scopes
        FROM akeneo_connectivity_connected_app AS connected_app
        JOIN akeneo_connectivity_connection AS connection ON connection.code = connected_app.connection_code AND connected_app.connection_code = :connectionCode
        JOIN pim_api_client AS client ON client.id = connection.client_id
        JOIN oro_user ON oro_user.id = connection.user_id
        LEFT JOIN pim_api_access_token AS access_token ON client.id = access_token.client
        LEFT JOIN pim_api_auth_code AS auth_code ON client.id = auth_code.client_id
        LEFT JOIN akeneo_connectivity_test_app AS test_app ON test_app.client_id = connected_app.id
        WHERE connection_code = :connectionCode
        SQL;

        $dataRow = $this->connection->executeQuery($selectQuery, ['connectionCode' => $connectionCode])->fetchAssociative();

        return $dataRow ? $this->denormalizeRow($dataRow) : null;
    }
}
