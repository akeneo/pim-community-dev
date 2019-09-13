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
class ListChildrenCategoriesWithCountIncludingSubCategories implements Query\ListChildrenCategoriesWithCountIncludingSubCategories
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
     * @param array    $categoryIdsInPath
     * @param string   $translationLocaleCode
     * @param int|null $categoryIdToSelectedAsFilter
     *
     * @return ChildCategory[]
     */
    private function getRecursivelyCategories(
        array $categoryIdsInPath,
        string $translationLocaleCode,
        ?int $categoryIdToSelectedAsFilter
    ) : array {
        $parentCategoryId = array_shift($categoryIdsInPath);
        $subchildCategoryId = $categoryIdsInPath[0] ?? null;

        $categoriesWithoutProductCount = $this->fetchChildrenCategories($parentCategoryId, $translationLocaleCode);
        $categoriesWithProductCount = $this->countProductInCategories($categoriesWithoutProductCount);


        $categories = [];
        foreach ($categoriesWithProductCount as $category) {
            $childrenCategoriesToExpand = null !== $subchildCategoryId && $subchildCategoryId === (int) $category['child_id'] ?
                $this->getRecursivelyCategories($categoryIdsInPath, $translationLocaleCode, $categoryIdToSelectedAsFilter): [];

            $isLeaf = count($category['children_codes']) === 0;
            $isUsedAsFilter = null !== $categoryIdToSelectedAsFilter ? (int) $category['child_id'] === $categoryIdToSelectedAsFilter: false;

            $categories[] = new ChildCategory(
                (int) $category['child_id'],
                $category['child_code'],
                $category['label'],
                $isUsedAsFilter,
                $isLeaf,
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
     *         'children_codes = ['child_1', 'child_2'],
     *         'label' => 'label'
     *     ]
     * ]
     */
    private function fetchChildrenCategories(
        int $parentCategoryId,
        string $translationLocaleCode
    ): array {
        $this->connection->exec('SET SESSION group_concat_max_len = 1000000');

        $sql = <<<SQL
            SELECT 
                child.id as child_id,
                child.code as child_code,
                COALESCE(ct.label, CONCAT('[', child.code, ']')) as label,
                GROUP_CONCAT(subchild.code ORDER BY subchild.lft) as children_codes
            FROM 
                pim_catalog_category child 
                JOIN pim_catalog_category subchild ON subchild.lft >= child.lft AND subchild.lft < child.rgt AND subchild.root = child.root
                LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = child.id AND ct.locale = :locale
            WHERE 
                child.parent_id = :parent_category_id
            GROUP BY 
                child.id,
                label
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
            $childrenCategoryCodes = null !== $row['children_codes'] ? explode(',', $row['children_codes']) : [];
            $row['children_codes'] = $childrenCategoryCodes;
            array_shift($row['children_codes']);

            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * @param array $categoriesWithoutCount
     *
     * [
     *     [
     *         'child_id' => 1,
     *         'child_code' => 'code',
     *         'children_codes = ['child_1', 'child_2'],
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
     *         'children_codes = ['child_1', 'child_2'],
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
            $categoryCodes = $category['children_codes'];
            $categoryCodes[] = $category['child_code'];
            // empty array needed for multisearch in ES
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            'categories' => $categoryCodes
                                        ]
                                    ],
                                    [
                                        'term' => [
                                            'document_type' => ProductInterface::class
                                        ]
                                    ]
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
