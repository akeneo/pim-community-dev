<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCountNotIncludingSubCategories implements Query\ListChildrenCategoriesWithCountNotIncludingSubCategories
{
    /** @var Connection */
    private $connection;

    /** @var Client */
    private $client;

    /**
     * @param Connection $connection
     * @param Client     $client
     */
    public function __construct(Connection $connection, Client $client)
    {
        $this->connection = $connection;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function list(
        string $translationLocaleCode,
        int $userId,
        int $categoryIdToExpand,
        ?int $categoryIdSelectedAsFilter
    ): array {
        $categoryIdsInPath = null !== $categoryIdSelectedAsFilter ?
            $this->fetchCategoriesBetween($categoryIdToExpand, $categoryIdSelectedAsFilter) : [$categoryIdToExpand];

        return $this->getRecursivelyCategories($categoryIdsInPath, $translationLocaleCode, $categoryIdSelectedAsFilter);
    }

    /**
     * Get recursively a tree until the selected category chosen as filter.
     * If category ids in path to expand are [A, B], it means you want to list
     * all children of A and then B.
     *
     *     B
     *     |--C
     *     |  |
     *     |  |
     *     |  |
     *     |  |
     *     |  C'
     *     |
     *     B'
     *
     *
     * It executes 1 SQL query and 1 ES query per level of depth of the category tree.
     * In the above example:
     * - it executes two requests(SQL +ES) to get children of A
     * - then, two requests to get children of B
     *
     * @param array     $categoryIdsInPath
     * @param string    $translationLocaleCode
     * @param int|null  $categoryIdToFilterWith
     *
     * @return ChildCategory[]
     */
    private function getRecursivelyCategories(
        array $categoryIdsInPath,
        string $translationLocaleCode,
        ?int $categoryIdToFilterWith
    ) : array {
        $parentCategoryId = array_shift($categoryIdsInPath);
        $subchildCategoryId = $categoryIdsInPath[0] ?? null;

        $categoriesWithoutCount = $this->fetchChildrenCategories($parentCategoryId, $translationLocaleCode);
        $categoriesWithCount = $this->countProductInCategories($categoriesWithoutCount);


        $categories = [];
        foreach ($categoriesWithCount as $category) {
            $childrenCategoriesToExpand = null !== $subchildCategoryId && $subchildCategoryId === (int) $category['child_id'] ?
                $this->getRecursivelyCategories($categoryIdsInPath, $translationLocaleCode, $categoryIdToFilterWith): [];

            $isUsedAsFilter = null !== $categoryIdToFilterWith ? (int) $category['child_id'] === $categoryIdToFilterWith: false;

            $categories[] = new ChildCategory(
                (int) $category['child_id'],
                $category['child_code'],
                $category['label'],
                $isUsedAsFilter,
                $category['is_leaf'],
                $category['count'],
                $childrenCategoriesToExpand
            );
        }

        return $categories;
    }

    /**
     * @param int    $parentCategoryId
     * @param string $translationLocaleCode
     *
     * @return array
     * [
     *     [
     *         'child_id' => 1,
     *         'child_code' => 'code',
     *         'is_leaf' = true,
     *         'label' => 'label'
     *     ]
     * ]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchChildrenCategories(
        int $parentCategoryId,
        string $translationLocaleCode
    ): array {
        $sql = <<<SQL
            SELECT 
                child.id as child_id,
                child.code as child_code,
                CASE 
                    WHEN child.lft + 1 = child.rgt THEN 1
                    ELSE 0
                END AS is_leaf,
                COALESCE(ct.label, CONCAT('[', child.code, ']')) as label
            FROM 
                pim_catalog_category child
                LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = child.id AND ct.locale = :locale
            WHERE 
                child.parent_id = :parent_category_id
            ORDER BY
                child.lft;
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parent_category_id' => $parentCategoryId,
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $row['is_leaf'] = 1 === (int) $row['is_leaf'];
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * @param array $categoriesWithoutCount
     * [
     *     [
     *         'child_id' => 1,
     *         'child_code' => 'code',
     *         'is_leaf = true,
     *         'label' => 'label'
     *     ]
     * ]
     *
     * @return array
     * [
     *     [
     *         'child_id' => 1,
     *         'child_code' => 'code',
     *         'label' => 'label',
     *         'is_leaf = true,
     *         'count' => 1
     *     ]
     * ]
     */
    private function countProductInCategories(array $categoriesWithoutCount): array
    {
        if (empty($categoriesWithoutCount)) {
            return [];
        }

        $body = [];
        foreach ($categoriesWithoutCount as $category) {
            // empty array needed for multisearch in ES
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    ['terms' => [
                                        'categories' => [$category['child_code']]
                                    ]],
                                    ['term' => [
                                        'document_type' => ProductInterface::class
                                    ]]
                                ]
                            ]
                        ]
                    ]
                ],
                'track_total_hits' => true,
            ];
        }

        $rows = $this->client->msearch($body);

        $categoriesWithCount = [];
        $index = 0;
        foreach ($categoriesWithoutCount as $category) {
            $category['count'] = $rows['responses'][$index]['hits']['total']['value'] ?? -1;
            $categoriesWithCount[] = $category;
            $index++;
        }

        return $categoriesWithCount;
    }


    /**
     * Returns all category ids between the category to expand (parent) and the category to filter with (subchild).
     * Example:
     *
     *          A
     *         / \
     *        B   C
     *       /     \
     *      D      E
     *
     * If category to expand is A and category to filter is D, it returns [A, B]
     *
     *
     * @param int $fromCategoryId
     * @param int $toCategoryId
     *
     * @return string[]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchCategoriesBetween(int $fromCategoryId, int $toCategoryId): array
    {
        $sql = <<<SQL
            SELECT 
                category_path.id
            FROM 
                pim_catalog_category parent
                JOIN pim_catalog_category category_path ON category_path.lft BETWEEN parent.lft AND parent.rgt AND parent.root = category_path.root
                JOIN pim_catalog_category subchild ON category_path.lft < subchild.lft AND category_path.rgt > subchild.lft AND parent.root = subchild.root
            WHERE 
                parent.id = :category_to_expand 
                AND subchild.id = :category_to_filter_with
            ORDER BY 
                category_path.lft
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'category_to_expand' => $fromCategoryId,
                'category_to_filter_with' => $toCategoryId,
            ]
        )->fetchAll();

        $ids = array_map(function ($row) {
            return (int) $row['id'];
        }, $rows);

        return $ids;
    }
}
