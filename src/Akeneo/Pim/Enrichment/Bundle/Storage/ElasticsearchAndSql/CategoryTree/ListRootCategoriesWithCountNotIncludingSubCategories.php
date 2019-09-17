<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountNotIncludingSubCategories implements Query\ListRootCategoriesWithCountNotIncludingSubCategories
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
        $categoriesWithoutCount = $this->getRootCategories($translationLocaleCode);
        $rootCategories = $this->countProductInCategories($categoriesWithoutCount, $rootCategoryIdToExpand);

        return $rootCategories;
    }

    /**
     * @param string $translationLocaleCode
     *
     * @return array
     * [
     *     [
     *         'root_id' => 1,
     *         'root_code' => 'code',
     *         'label' => 'label'
     *     ]
     * ]
     */
    private function getRootCategories(string $translationLocaleCode): array
    {
        $sql = <<<SQL
            SELECT 
                root.id as root_id,
                root.code as root_code,
                COALESCE(ct.label, CONCAT('[', root.code, ']')) as label
            FROM 
                pim_catalog_category root
                LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = root.id AND ct.locale = :locale
            WHERE 
                root.parent_id IS NULL
            ORDER BY 
                label, root.code
SQL;

        $categories = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();

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
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'terms' => [
                                'categories' => [$category['root_code']]
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
