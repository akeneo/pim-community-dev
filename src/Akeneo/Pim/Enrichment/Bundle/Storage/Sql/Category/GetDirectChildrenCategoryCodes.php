<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
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

    /**
     * @return array<string, array{code: string, row_num: int}>
     */
    public function execute(int $categoryId): array
    {
        $sql = <<<SQL
          SELECT code,ROW_NUMBER() over (order by lft) as row_num
          FROM pim_catalog_category
          WHERE parent_id = :category_id
        SQL;

        return $this->connection->executeQuery($sql, ['category_id' => $categoryId])->fetchAllAssociativeIndexed();
    }
}
