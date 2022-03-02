<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAccessTokenQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAccessTokenQuery implements GetAccessTokenQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $clientId, array $scopes = []): ?string
    {
        $query = <<<SQL
SELECT token.token
FROM pim_api_access_token as token
LEFT JOIN pim_api_client as client ON token.client = client.id AND client.marketplace_public_app_id = :client_id
LEFT JOIN akeneo_connectivity_connected_app as app ON app.id = client.marketplace_public_app_id
WHERE JSON_CONTAINS(app.scopes, :scopes) AND JSON_LENGTH(app.scopes) = :scopesCount
SQL;

        $token = $this->connection->fetchOne(
            $query,
            [
                'client_id' => $clientId,
                'scopes' => \json_encode($scopes),
                'scopesCount' => \count($scopes),
            ]
        );

        return false !== $token ? $token : null;
    }
}
