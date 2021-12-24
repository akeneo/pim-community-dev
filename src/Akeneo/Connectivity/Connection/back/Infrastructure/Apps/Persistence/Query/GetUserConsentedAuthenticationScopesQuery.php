<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetUserConsentedAuthenticationScopesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserConsentedAuthenticationScopesQuery implements GetUserConsentedAuthenticationScopesQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(int $userId, string $appId): array
    {
        $query = <<<SQL
            SELECT scopes FROM akeneo_connectivity_user_consent
            WHERE user_id = :userId AND app_id = :appId
            SQL;

        $scopes = $this->connection->fetchOne($query, [
            'userId' => $userId,
            'appId' => $appId,
        ]);

        if (!$scopes) {
            return [];
        }

        return \json_decode($scopes);
    }
}
