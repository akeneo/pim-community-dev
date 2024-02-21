<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllPendingAppsPublicIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllPendingAppsPublicIdsQuery implements GetAllPendingAppsPublicIdsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): array
    {
        $sql = <<<SQL
SELECT marketplace_public_app_id
FROM pim_api_client
LEFT JOIN pim_api_access_token as access_token ON pim_api_client.id = access_token.client
LEFT JOIN pim_api_auth_code as auth_code ON pim_api_client.id = auth_code.client_id
WHERE access_token.token IS NULL AND auth_code.token IS NOT NULL
SQL;

        return $this->connection->fetchFirstColumn($sql);
    }
}
