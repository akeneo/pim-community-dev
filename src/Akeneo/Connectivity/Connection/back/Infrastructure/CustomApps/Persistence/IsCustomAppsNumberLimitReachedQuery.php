<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service\GetCustomAppsNumberLimit;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedQuery implements IsCustomAppsNumberLimitReachedQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetCustomAppsNumberLimit $getCustomAppsNumberLimit,
    ) {
    }

    public function execute(): bool
    {
        $sql = <<<SQL
SELECT COUNT(*) as count
FROM akeneo_connectivity_test_app;
SQL;

        $customAppsCount = (int) $this->connection->executeQuery($sql)->fetchOne();

        return $customAppsCount >= $this->getCustomAppsNumberLimit->getLimit();
    }
}
