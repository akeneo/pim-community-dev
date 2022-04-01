<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteAccessTokensQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAccessTokensQuery implements DeleteAccessTokensQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $appId): int
    {
        $query = <<<SQL
        DELETE token 
        FROM pim_api_access_token as token
        JOIN pim_api_client as client ON token.client = client.id AND client.marketplace_public_app_id = :app_id
        SQL;

        $statement = $this->connection->executeQuery($query, [
            'app_id' => $appId,
        ]);

        return $statement->rowCount();
    }
}
