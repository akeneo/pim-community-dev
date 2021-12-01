<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserConsentQuery implements CreateUserConsentQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(int $userId, string $appId, array $authenticationScopes, \DateTimeImmutable $consentDate): void
    {
        $query = <<<SQL
            INSERT INTO akeneo_connectivity_user_consent (user_id, app_id, scopes, consent_date)
            VALUES (:userId, :appId, :scopes, :consentDate)
            SQL;

        $this->connection->executeQuery($query, [
            'userId' => $userId,
            'appId' => $appId,
            'scopes' => $authenticationScopes,
            'consentDate' => $consentDate,
        ], [
            'userId' => Types::INTEGER,
            'scopes' => Types::JSON,
            'consentDate' => Types::DATETIMETZ_IMMUTABLE
        ]);
    }
}
