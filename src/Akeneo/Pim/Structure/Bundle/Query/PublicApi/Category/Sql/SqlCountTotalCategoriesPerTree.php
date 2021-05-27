<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Category\Sql;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\CategoryTree;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\CountTotalCategoriesPerTree;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\FindCategoryTrees;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
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
            : $this->countWithoutChildren($selectedCategories,);
    }

    private function countWithChildren(array $selectedCategories): array
    {
        return [];
    }

    private function countWithoutChildren(array $selectedCategories): array
    {
        $query = <<<SQL
SELECT r.code, COUNT(*)
FROM pim_catalog_category c INNER JOIN pim_catalog_category r ON c.root = r.id
WHERE c.code IN (:selectedCategories)
GROUP BY c.root;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategories],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
