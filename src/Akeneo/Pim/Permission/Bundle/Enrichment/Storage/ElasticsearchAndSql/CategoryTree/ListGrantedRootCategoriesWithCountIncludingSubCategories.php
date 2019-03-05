<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
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

    /** @var string */
    private $indexType;

    /**
     * @param Connection $connection
     * @param Client     $client
     * @param string     $indexType
     */
    public function __construct(Connection $connection, Client $client, string $indexType)
    {
        $this->connection = $connection;
        $this->client = $client;
        $this->indexType = $indexType;
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
        $sql = <<<SQL
            SELECT
                root.id as root_id,
                root.code as root_code, 
                child.children_codes,
                COALESCE(ct.label, CONCAT('[', root.code, ']')) as label
            FROM 
                pim_catalog_category AS root
                LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = root.id AND ct.locale = :locale
                LEFT JOIN
                (
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
                                AND ag.user_id = :user_id_1
                        ) 
                    GROUP BY 
                        child.root
                ) AS child ON root.id = child.root_id
            WHERE 
                root.parent_id IS NULL
                AND EXISTS (
                    SELECT * FROM pimee_security_product_category_access ca
                    JOIN oro_user_access_group ag ON ag.group_id = ca.user_group_id
                    WHERE
                        ca.category_id = root.id
                        AND ca.view_items = 1
                        AND ag.user_id = :user_id_2
                ) 
            ORDER BY 
                label, root.code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode,
                'user_id_1' => $userId,
                'user_id_2' => $userId
            ]
        )->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $row['children_codes'] = null !== $row['children_codes'] ? json_decode($row['children_codes'], true) : [];
            ;
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
                            'terms' => [
                                'categories' => $categoryCodes
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $rootCategories = [];
        $index = 0;
        foreach ($categoriesWithoutCount as $category) {
            $rootCategories[] = new RootCategory(
                (int) $category['root_id'],
                $category['root_code'],
                $category['label'],
                $rows['responses'][$index]['hits']['total'] ?? -1,
                (int) $category['root_id'] === $rootCategoryIdToExpand
            );

            $index++;
        }

        return $rootCategories;
    }
}
