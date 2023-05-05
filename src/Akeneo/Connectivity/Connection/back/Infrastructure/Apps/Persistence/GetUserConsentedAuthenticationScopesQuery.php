<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUserConsentedAuthenticationScopesQuery implements GetUserConsentedAuthenticationScopesQueryInterface
{
    public function __construct(private Connection $connection)
    {
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

        return \json_decode($scopes, null, 512, JSON_THROW_ON_ERROR);
    }
}
