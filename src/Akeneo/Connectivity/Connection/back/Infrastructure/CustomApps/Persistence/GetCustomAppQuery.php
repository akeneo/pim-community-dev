<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppQuery implements GetCustomAppQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     author: string|null,
     *     activate_url: string,
     *     callback_url: string,
     *     connected: bool,
     * }|null
     */
    public function execute(string $id): ?array
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
        WHERE app.client_id = :id
        SQL;

        $result = $this->connection->fetchAssociative(
            $query,
            [
                'id' => $id,
            ]
        );

        if (!$result) {
            return null;
        }

        $result['connected'] = (bool) $result['connected'];

        return $result;
    }
}
