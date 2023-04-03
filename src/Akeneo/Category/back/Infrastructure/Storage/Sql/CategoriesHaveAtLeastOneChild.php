<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\ServiceApi\Query\CategoriesHaveAtLeastOneChild as BaseCategoriesHaveAtLeastOneChild;
use Doctrine\DBAL\Connection;

/**
 * @see https://en.wikipedia.org/wiki/Nested_set_model
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesHaveAtLeastOneChild implements BaseCategoriesHaveAtLeastOneChild
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function among(array $parentCategoryCodes, array $childrenCategoryCodes): bool
    {
        if (\count($parentCategoryCodes) === 0 || \count($childrenCategoryCodes) === 0) {
            return false;
        }

        $categoryInfos = $this->getCategoryInfos(\array_merge($parentCategoryCodes, $childrenCategoryCodes));

        $parentsCategoryInfos = \array_filter(
            \array_map(static fn (string $code): ?array => $categoryInfos[$code] ?? null, $parentCategoryCodes),
        );
        $childrenCategoryInfos = \array_filter(
            \array_map(static fn (string $code): ?array => $categoryInfos[$code] ?? null, $childrenCategoryCodes),
        );

        foreach ($parentsCategoryInfos as $parentCategoryInfo) {
            foreach ($childrenCategoryInfos as $childrenCategoryInfo) {
                if ($childrenCategoryInfo['root'] === $parentCategoryInfo['root'] &&
                    $childrenCategoryInfo['lft'] >= $parentCategoryInfo['lft'] &&
                    $childrenCategoryInfo['rgt'] <= $parentCategoryInfo['rgt']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string[] $categoryCodes
     *
     * @return array{
     *     code: string,
     *     lft: int,
     *     rgt: int,
     *     root: int,
     * }[]
     */
    private function getCategoryInfos(array $categoryCodes): array
    {
        $sql = <<<SQL
SELECT code, lft, rgt, root
FROM pim_catalog_category
WHERE code IN (:categoryCodes)
SQL;

        $result = [];
        foreach ($this->connection->fetchAllAssociative(
            $sql,
            ['categoryCodes' => $categoryCodes],
            ['categoryCodes' => Connection::PARAM_STR_ARRAY],
        ) as $row) {
            $result[$row['code']] = [
                'lft' => \intval($row['lft']),
                'rgt' => \intval($row['rgt']),
                'root' => \intval($row['root']),
            ];
        }

        return $result;
    }
}
