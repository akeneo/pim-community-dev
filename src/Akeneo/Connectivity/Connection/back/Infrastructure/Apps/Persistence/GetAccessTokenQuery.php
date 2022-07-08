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

    public function execute(string $appId, string $scopes): ?string
    {
        $query = <<<SQL
        SELECT token.token
        FROM pim_api_access_token as token
        JOIN pim_api_client as client ON token.client = client.id AND client.marketplace_public_app_id = :app_id
        WHERE token.scope = :scopes
        SQL;

        $token = $this->connection->fetchOne(
            $query,
            [
                'app_id' => $appId,
                'scopes' => $scopes,
            ]
        );

        return false !== $token ? $token : null;
    }
}
