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
class SqlCountTotalCategoriesPerTree implements CountTotalCategoriesPerTree
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $selectedCategories, bool $withChildren): array
    {
        Assert::allStringNotEmpty($selectedCategories);

        return $withChildren ?
            $this->countWithChildren($selectedCategories)
            : $this->countWithoutChildren($selectedCategories);
    }

    private function countWithChildren(array $selectedCategories): array
    {
        $query = <<<SQL
SELECT c.code, COALESCE(result.selectedCount, 0)
FROM pim_catalog_category c LEFT JOIN (
	SELECT root.id as id, root.code as code, COUNT(*) as selectedCount
	FROM pim_catalog_category parent
	JOIN pim_catalog_category child ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
	JOIN pim_catalog_category root ON root.id = child.root
	WHERE parent.code IN (:selectedCategories)
	GROUP BY root.id
) result ON result.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategories],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private function countWithoutChildren(array $selectedCategories): array
    {
        $query = <<<SQL
SELECT c.code, COALESCE(result.selectedCount, 0)
FROM pim_catalog_category c LEFT JOIN (
	SELECT cr.id as id, cr.code as code, COUNT(*) as selectedCount
	FROM pim_catalog_category c JOIN pim_catalog_category cr ON c.root = cr.id
	WHERE c.code IN (:selectedCategories)
	GROUP BY c.root
) result ON result.id = c.id
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
