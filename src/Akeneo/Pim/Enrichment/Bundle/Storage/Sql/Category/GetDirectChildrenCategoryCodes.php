<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetDirectChildrenCategoryCodes implements GetDirectChildrenCategoryCodesInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(CategoryInterface $category): array
    {
        $sql = <<<SQL
            SELECT child.code
            FROM pim_catalog_category parent
            JOIN pim_catalog_category child ON child.lft > parent.lft AND child.lft < parent.rgt AND child.root = parent.root AND child.parent_id = :category_id
            WHERE parent.id = :category_id
            ORDER BY child.lft;
SQL;

        return $this->connection->executeQuery($sql, ['category_id' => $category->getId()])->fetchFirstColumn();
    }
}
