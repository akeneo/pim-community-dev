<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Category\Api\GetCategoryChildrenCodesPerTreeInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CategoryCodeFilterInterface;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetCategoryChildrenCodesPerTree implements GetCategoryChildrenCodesPerTreeInterface
{
    private Connection $connection;
    private CategoryCodeFilterInterface $categoryCodeFilter;

    public function __construct(Connection $connection, CategoryCodeFilterInterface $categoryCodeFilter)
    {
        $this->connection = $connection;
        $this->categoryCodeFilter = $categoryCodeFilter;
    }

    public function executeWithChildren(array $categoryCodes): array
    {
        Assert::allStringNotEmpty($categoryCodes);

        $query = <<<SQL
WITH categoriesByTreeCount (id, code, childrenCodes) AS (
    SELECT root.id as id, root.code as code, JSON_ARRAYAGG(child.code)
    FROM pim_catalog_category parent
             JOIN pim_catalog_category child
                  ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
             JOIN pim_catalog_category root
                  ON root.id = child.root
    WHERE parent.code IN (:categoryCodes)
    GROUP BY root.id
)
SELECT c.code, COALESCE(categoriesByTreeCount.childrenCodes, '[]') AS children_codes
FROM pim_catalog_category c
    LEFT JOIN categoriesByTreeCount
        ON categoriesByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['categoryCodes' => $categoryCodes],
            ['categoryCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        while ($result = $stmt->fetchAssociative()) {
            $childrenCodes = array_unique(json_decode($result['children_codes'], true));

            $results[$result['code']] = $this->categoryCodeFilter->filter($childrenCodes);
        }

        return $results;
    }

    public function executeWithoutChildren(array $categoryCodes): array
    {
        Assert::allStringNotEmpty($categoryCodes);

        $query = <<<SQL
WITH categoriesByTreeCount (id, childrenCodes) AS (
    SELECT
           root.id as id,
           JSON_ARRAYAGG(child.code)
    FROM pim_catalog_category child
        JOIN pim_catalog_category root
            ON child.root = root.id
    WHERE child.code IN (:categoryCodes)
    GROUP BY child.root
)
SELECT c.code, COALESCE(categoriesByTreeCount.childrenCodes, '[]') AS children_codes
FROM pim_catalog_category c
    LEFT JOIN categoriesByTreeCount
        ON categoriesByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['categoryCodes' => $categoryCodes],
            ['categoryCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        while ($result = $stmt->fetchAssociative()) {
            $childrenCodes = array_unique(json_decode($result['children_codes'], true));

            $results[$result['code']] = $this->categoryCodeFilter->filter($childrenCodes);
        }

        return $results;
    }
}
