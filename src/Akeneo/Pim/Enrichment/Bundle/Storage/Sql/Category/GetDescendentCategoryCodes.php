<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Returns codes of all descendents of the given category.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetDescendentCategoryCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(CategoryInterface $parentCategory): array
    {
        $sql = <<<SQL
            SELECT category.code as category_code
            FROM pim_catalog_category category 
            WHERE category.lft > :parent_category_left
            AND category.rgt < :parent_category_right
            AND category.root = :parent_category_root;
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parent_category_left'  => $parentCategory->getLeft(),
                'parent_category_right' => $parentCategory->getRight(),
                'parent_category_root'  => $parentCategory->getRoot(),
            ]
        )->fetchAll(\PDO::FETCH_COLUMN);

        return $rows;
    }
}
