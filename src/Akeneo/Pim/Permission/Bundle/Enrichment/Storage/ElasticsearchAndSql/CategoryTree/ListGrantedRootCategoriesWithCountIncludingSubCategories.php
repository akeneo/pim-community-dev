<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListGrantedRootCategoriesWithCountIncludingSubCategories implements Query\ListRootCategoriesWithCountIncludingSubCategories
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
    public function list(string $translationLocaleCode, int $userId, int $rootCategoryIdToExpand): array
    {
        $categoriesWithoutCount = $this->getRootCategories($userId, $translationLocaleCode);
        $rootCategories = $this->countProductInCategories($categoriesWithoutCount, $rootCategoryIdToExpand);

        return $rootCategories;
    }

    /**
     * It gets the children categories into a dedicated query instead of a CTE or a subquery to avoid
     * to order by the final results with a potential big array of children category codes.
     *
     * Such ordering would fail on some big catalogs. The first attempt was to increase the sort buffer size, but
     * a customer reached the limit. Splitting the queries fixes definitively the issue with any number of children categories.
     *
     * @param int    $userId
     * @param string $translationLocaleCode
     *
     * @return array
     * [
     *     [
     *         'root_id' => 1,
     *         'root_code' => 'code',
     *         'children_codes = ['child_1', 'child_2'],
     *         'label' => 'label'
     *     ]
     * ]
     */
    private function getRootCategories(int $userId, string $translationLocaleCode): array
    {
        $childrenCategoriesSql = <<<SQL
            SELECT 
                child.root as root_id,
                JSON_ARRAYAGG(child.code) as children_codes
            FROM 
                pim_catalog_category child
            WHERE 
                child.parent_id IS NOT NULL
               AND EXISTS (
                    SELECT * FROM pimee_security_product_category_access ca
                    JOIN oro_user_access_group ag ON ag.group_id = ca.user_group_id
                    WHERE
                        ca.category_id = child.id
                        AND ca.view_items = 1
                        AND ag.user_id = :user_id
                ) 
            GROUP BY 
                child.root
        SQL;

        $childrenCategoriesRows = $this->connection->executeQuery($childrenCategoriesSql, ['user_id' => $userId])->fetchAll();
        $childrenCategoriesIndexedByRootId = array_combine(
            array_column($childrenCategoriesRows, 'root_id'),
            array_column($childrenCategoriesRows, 'children_codes')
        );

        $sql = <<<SQL
            SELECT
                root.id as root_id,
                root.code as root_code, 
                COALESCE(ct.label, CONCAT('[', root.code, ']')) as label
            FROM 
                pim_catalog_category AS root
                LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = root.id AND ct.locale = :locale
            WHERE 
                root.parent_id IS NULL
                AND EXISTS (
                    SELECT * FROM pimee_security_product_category_access ca
                    JOIN oro_user_access_group ag ON ag.group_id = ca.user_group_id
                    WHERE
                        ca.category_id = root.id
                        AND ca.view_items = 1
                        AND ag.user_id = :user_id
                ) 
            ORDER BY 
                label, root.code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode,
                'user_id' => $userId
            ]
        )->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $row['children_codes'] = isset($childrenCategoriesIndexedByRootId[$row['root_id']]) ? json_decode($childrenCategoriesIndexedByRootId[$row['root_id']], true) : [];
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * @param array $categoriesWithoutCount
     * @param int   $rootCategoryIdToExpand
     *
     * @return RootCategory[]
     */
    private function countProductInCategories(array $categoriesWithoutCount, int $rootCategoryIdToExpand): array
    {
        if (empty($categoriesWithoutCount)) {
            return [];
        }

        $body = [];
        foreach ($categoriesWithoutCount as $category) {
            $categoryCodes = $category['children_codes'];
            $categoryCodes[] = $category['root_code'];
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    ['terms' => [
                                        'categories' => $categoryCodes
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

        $rootCategories = [];
        $index = 0;
        foreach ($categoriesWithoutCount as $category) {
            $rootCategories[] = new RootCategory(
                (int) $category['root_id'],
                $category['root_code'],
                $category['label'],
                $rows['responses'][$index]['hits']['total']['value'] ?? -1,
                (int) $category['root_id'] === $rootCategoryIdToExpand
            );

            $index++;
        }

        return $rootCategories;
    }
}
