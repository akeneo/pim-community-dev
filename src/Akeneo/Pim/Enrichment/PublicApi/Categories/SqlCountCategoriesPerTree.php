<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\PublicApi\Categories;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCountCategoriesPerTree implements CountCategoriesPerTree
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function executeWithChildren(array $selectedCategories): array
    {
        Assert::allStringNotEmpty($selectedCategories);

        $query = <<<SQL
WITH categoriesSelectedByTreeCount (id, code, count) AS (
    SELECT root.id as id, root.code as code, COUNT(*) as count
    FROM pim_catalog_category parent
        JOIN pim_catalog_category child
            ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
        JOIN pim_catalog_category root
            ON root.id = child.root
    WHERE parent.code IN (:selectedCategories)
    GROUP BY root.id
)
SELECT c.code, COALESCE(categoriesSelectedByTreeCount.count, 0)
FROM pim_catalog_category c
    LEFT JOIN categoriesSelectedByTreeCount
        ON categoriesSelectedByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategories],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function executeWithoutChildren(array $selectedCategories): array
    {
        Assert::allStringNotEmpty($selectedCategories);

        $query = <<<SQL
WITH categoriesSelectedByTreeCount (id, selectedCategories) AS (
    SELECT
           root.id as id,
           COUNT(child.id) as selectedCategories
    FROM pim_catalog_category child
        JOIN pim_catalog_category root
            ON child.root = root.id
    WHERE child.code IN (:selectedCategories)
    GROUP BY child.root
)
SELECT c.code, COALESCE(categoriesSelectedByTreeCount.selectedCategories, 0)
FROM pim_catalog_category c
    LEFT JOIN categoriesSelectedByTreeCount
        ON categoriesSelectedByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategories],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
