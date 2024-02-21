<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\HasUserConsentForAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUserConsentForAppQuery implements HasUserConsentForAppQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(int $userId, string $appId): bool
    {
        $query = <<<SQL
            SELECT COUNT(*) FROM akeneo_connectivity_user_consent
            WHERE user_id = :userId AND app_id = :appId
            SQL;

        return (bool) $this->connection->fetchOne($query, [
            'userId' => $userId,
            'appId' => $appId,
        ]);
    }
}
