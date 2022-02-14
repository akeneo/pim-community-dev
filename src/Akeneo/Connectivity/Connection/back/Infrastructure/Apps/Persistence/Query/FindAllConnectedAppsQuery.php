<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\FindAllConnectedAppsQueryInterface;
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
               IF(akeneo_connectivity_test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app
        FROM akeneo_connectivity_connected_app AS connected_app
        LEFT JOIN akeneo_connectivity_test_app ON akeneo_connectivity_test_app.client_id = connected_app.id
        ORDER BY name ASC
        SQL;

        $dataRows = $this->connection->executeQuery($selectSQL)->fetchAll();

        $connectedApps = array_map(
            fn ($dataRow) => $this->denormalizeRow($dataRow),
            $dataRows
        );

        return $connectedApps;
    }
}
