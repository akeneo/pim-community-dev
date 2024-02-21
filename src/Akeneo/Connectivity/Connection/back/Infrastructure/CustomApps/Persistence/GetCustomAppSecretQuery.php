<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCustomAppSecretQuery implements GetCustomAppSecretQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(string $clientId): ?string
    {
        $sql = <<<SQL
        SELECT client_secret
        FROM akeneo_connectivity_test_app
        WHERE client_id = :clientId
        SQL;

        $secret = $this->connection->fetchOne($sql, ['clientId' => $clientId]);

        return \is_string($secret) ? $secret : null;
    }
}
