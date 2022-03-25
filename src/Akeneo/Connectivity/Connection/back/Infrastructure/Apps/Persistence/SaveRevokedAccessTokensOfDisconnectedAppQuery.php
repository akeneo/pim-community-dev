<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveRevokedAccessTokensOfDisconnectedAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SaveRevokedAccessTokensOfDisconnectedAppQuery implements SaveRevokedAccessTokensOfDisconnectedAppQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $appId): void
    {
        $query = <<<SQL
            INSERT INTO akeneo_connectivity_revoked_app_token (`token`)
            SELECT access_token.token
            FROM pim_api_access_token access_token
            JOIN pim_api_client client ON client.id = access_token.client
            JOIN akeneo_connectivity_connection connection ON connection.client_id = client.id
            JOIN akeneo_connectivity_connected_app app ON connection.code = app.connection_code
            WHERE app.id = :app_id
            ON DUPLICATE KEY UPDATE `token` = access_token.token
            SQL;

        $this->connection->executeQuery($query, [
            'app_id' => $appId,
        ]);
    }
}
