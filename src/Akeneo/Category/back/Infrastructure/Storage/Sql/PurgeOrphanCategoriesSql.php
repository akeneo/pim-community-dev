<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\PurgeOrphanCategories;
use Doctrine\DBAL\Connection;

/**
 * Delete all orphan categories with their children, those without parents and which aren't tree (id = root).
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PurgeOrphanCategoriesSql implements PurgeOrphanCategories
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function execute(): int
    {
        $sql = <<< SQL
            WITH RECURSIVE orphan_categories as (
                SELECT id, code, parent_id
                FROM pim_catalog_category AS children
                WHERE parent_id IS NULL AND root != id
                UNION
                SELECT pim_catalog_category.id, pim_catalog_category.code, pim_catalog_category.parent_id
                FROM pim_catalog_category
                INNER JOIN orphan_categories parent ON parent.id = pim_catalog_category.parent_id
            )
            DELETE
            FROM pim_catalog_category
            USING pim_catalog_category
            INNER JOIN orphan_categories ON pim_catalog_category.id = orphan_categories.id;
        SQL;

        return $this->connection->executeQuery($sql)->rowCount();
    }
}
