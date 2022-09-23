<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\IsCatalogsNumberLimitReachedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsCatalogsNumberLimitReachedQuery implements IsCatalogsNumberLimitReachedQueryInterface
{
    public function __construct(
        private Connection $connection,
        private int $limit,
    ) {
    }

    public function execute(string $ownerUsername): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM akeneo_catalog catalog
            JOIN oro_user user ON user.id = catalog.owner_id
            WHERE user.username = :owner_username;
        SQL;

        $catalogCount = (int) $this->connection->executeQuery($sql, ['owner_username' => $ownerUsername])->fetchOne();

        return $catalogCount >= $this->limit;
    }
}
