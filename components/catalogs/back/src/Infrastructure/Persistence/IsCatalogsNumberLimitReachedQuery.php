<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Persistence\IsCatalogsNumberLimitReachedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCatalogsNumberLimitReachedQuery implements IsCatalogsNumberLimitReachedQueryInterface
{
    public function __construct(
        private Connection $connection,
        private int $limit,
    ) {
    }

    public function execute(int $ownerId): bool
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM akeneo_catalog
            WHERE owner_id = :owner_id;
        SQL;

        $catalogCount = (int) $this->connection->executeQuery($sql, ['owner_id' => $ownerId])->fetchOne();

        return $catalogCount >= $this->limit;
    }
}
