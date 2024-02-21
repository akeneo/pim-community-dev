<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetAllCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCustomAppsQuery implements GetAllCustomAppsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): GetAllCustomAppsResult
    {
        $query = <<<SQL
        SELECT 
            app.client_id AS id,
            app.name,
            IF(app.user_id IS NOT NULL, CONCAT_WS(' ', user.name_prefix, user.first_name, user.middle_name, user.last_name, user.name_suffix), NULL) AS author,
            app.activate_url,
            app.callback_url,
            (
                SELECT COUNT(connected_app.id)
                FROM akeneo_connectivity_connected_app connected_app
                WHERE connected_app.id = app.client_id
            ) AS connected
        FROM akeneo_connectivity_test_app AS app
        LEFT JOIN oro_user user on user.id = app.user_id
        SQL;

        $rows = $this->connection->fetchAllAssociative($query);

        return GetAllCustomAppsResult::create(
            \count($rows),
            \array_map(function ($row): App {
                $row['connected'] = (bool) $row['connected'];

                return App::fromCustomAppValues($row);
            }, $rows)
        );
    }
}
