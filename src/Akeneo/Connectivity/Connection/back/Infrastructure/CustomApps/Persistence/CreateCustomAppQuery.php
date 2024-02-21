<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\CreateCustomAppQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCustomAppQuery implements CreateCustomAppQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(
        string $clientId,
        string $name,
        string $activateUrl,
        string $callbackUrl,
        string $clientSecret,
        int $userId,
    ): void {
        $sql = <<<SQL
        INSERT INTO akeneo_connectivity_test_app (name, activate_url, callback_url, client_secret, client_id, user_id)
        VALUES (:name, :activateUrl, :callbackUrl, :clientSecret, :clientId, :userId)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'clientId' => $clientId,
                'name' => $name,
                'activateUrl' => $activateUrl,
                'callbackUrl' => $callbackUrl,
                'clientSecret' => $clientSecret,
                'userId' => $userId,
            ]
        );
    }
}
