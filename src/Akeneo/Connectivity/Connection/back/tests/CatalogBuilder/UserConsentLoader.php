<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserConsentLoader
{
    public function __construct(private Connection $connection)
    {
    }

    public function addUserConsent(
        int $userId,
        string $appId,
        array $scopes,
        UuidInterface $uuid,
        \DateTimeImmutable $consentDate
    ): void {
        $query = <<<SQL
            INSERT INTO akeneo_connectivity_user_consent (`user_id`,`app_id`,`scopes`, `uuid`,`consent_date`)
            VALUES (:userId, :appId, :scopes, :uuid, :consentDate)
            SQL;

        $this->connection->executeQuery($query, [
            'userId' => $userId,
            'appId' => $appId,
            'scopes' => $scopes,
            'uuid' => $uuid->toString(),
            'consentDate' => $consentDate,
        ], [
            'userId' => Types::INTEGER,
            'appId' => Types::STRING,
            'scopes' => Types::JSON,
            'uuid' => Types::ASCII_STRING,
            'consentDate' => Types::DATETIMETZ_IMMUTABLE,
        ]);
    }
}
