<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppSecretQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetTestAppSecretQuery implements GetTestAppSecretQueryInterface
{
    public function __construct(private Connection $connection)
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
