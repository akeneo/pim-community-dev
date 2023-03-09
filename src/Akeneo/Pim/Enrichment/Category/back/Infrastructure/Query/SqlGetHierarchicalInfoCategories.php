<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetHierarchicalInfoCategories;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetHierarchicalInfoCategories implements GetHierarchicalInfoCategories
{

    public function __construct(private readonly Connection $connection)
    {
    }

    public function isAChildOf(string $parentCategoryCodes, string $childrenCategoryCodes): bool
    {
        $sql = <<<SQL
SELECT child.code
FROM pim_catalog_category AS child
 JOIN pim_catalog_category AS parent
  ON child.lft > parent.lft 
    AND child.rgt < parent.rgt 
    AND child.root = parent.root
WHERE child.code = :children_category_code AND parent.code = :parent_category_code;
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parent_category_code' => $parentCategoryCodes,
                'children_category_code' => $childrenCategoryCodes
            ]
        )->fetchAllAssociative();

        return count($rows) > 0;
    }
}
