<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\CountTreesChildrenInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountTreesChildren implements CountTreesChildrenInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT tree.code,
   (
       SELECT COUNT(*) 
       FROM pim_catalog_category AS children 
       WHERE children.root = tree.id
       AND children.parent_id IS NOT NULL
   ) AS count_children
FROM pim_catalog_category AS tree
WHERE tree.parent_id IS NULL;
SQL;
        $stmt = $this->dbConnection->executeQuery($query);

        $treesChildren = [];
        while ($tree = $stmt->fetchAssociative()) {
            $treesChildren[$tree['code']] = intval($tree['count_children']);
        }

        return $treesChildren;
    }
}
