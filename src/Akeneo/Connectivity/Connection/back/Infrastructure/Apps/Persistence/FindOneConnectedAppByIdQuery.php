<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DenormalizeConnectedAppTrait;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneConnectedAppByIdQuery implements FindOneConnectedAppByIdQueryInterface
{
    use DenormalizeConnectedAppTrait;

    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $appId): ?ConnectedApp
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
            IF(test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app,
            connected_app.has_outdated_scopes
        FROM akeneo_connectivity_connected_app connected_app
        JOIN akeneo_connectivity_connection connection ON connection.code = connected_app.connection_code
        JOIN oro_user ON oro_user.id = connection.user_id
        LEFT JOIN akeneo_connectivity_test_app test_app ON test_app.client_id = connected_app.id
        WHERE connected_app.id = :id
        SQL;

        $dataRow = $this->connection->executeQuery($selectQuery, ['id' => $appId])->fetchAssociative();

        return $dataRow ? $this->denormalizeRow($dataRow) : null;
    }
}
