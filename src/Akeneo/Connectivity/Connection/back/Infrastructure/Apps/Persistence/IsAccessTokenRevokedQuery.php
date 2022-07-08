<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\IsAccessTokenRevokedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsAccessTokenRevokedQuery implements IsAccessTokenRevokedQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $token): bool
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_connectivity_revoked_app_token
WHERE token = :token
SQL;

        return (bool) $this->connection->fetchOne($query, [
            'token' => $token,
        ]);
    }
}
