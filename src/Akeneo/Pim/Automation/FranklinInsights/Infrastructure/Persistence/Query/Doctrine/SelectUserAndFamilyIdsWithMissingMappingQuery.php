<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Doctrine\DBAL\Connection;

/**
 * This query returns a list of family IDs corresponding to attribute mappings
 * having pending attributes.
 * Those family IDs are grouped by user IDs, those of the users owning products
 * of those families, and so being able to complete the corresponding attribute
 * mappings.
 *
 * The data returned are formatted as follow:
 * [
 *     [
 *         'user_id' => 1,
 *         'family_ids' => [42, 43],
 *     ],
 *     [
 *         'user_id' => 2,
 *         'family_ids' => [42, 44, 45],
 *     ],
 * ]
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectUserAndFamilyIdsWithMissingMappingQuery
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Merges the results of the 2 queries and ensure uniqueness of the results.
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function execute(): array
    {
        $idsForClassifiedProducts = $this->getUserAndFamilyIdsForClassifiedProducts();
        $idsForUnclassifiedProducts = $this->getUserAndFamilyIdsForUnclassifiedProducts();

        return array_merge($idsForClassifiedProducts, $idsForUnclassifiedProducts);
    }

    /**
     * Returns the family IDS of classified products. This means users have own
     * permission on at least one product of each family it is associated with.
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function getUserAndFamilyIdsForClassifiedProducts(): array
    {
        $sql = <<<SQL
SELECT uag.user_id         AS user_id,
       JSON_ARRAYAGG(f.id) AS family_ids
FROM pimee_franklin_insights_subscription s
     INNER JOIN pim_catalog_product p ON p.id = s.product_id
     INNER JOIN pim_catalog_family f ON f.id = p.family_id
     INNER JOIN pim_catalog_category_product cp ON p.id = cp.product_id
     INNER JOIN pimee_security_product_category_access pca
                ON pca.category_id = cp.category_id AND pca.own_items IS TRUE
     INNER JOIN oro_user_access_group uag ON uag.group_id = pca.user_group_id
WHERE s.misses_mapping IS TRUE
GROUP BY uag.user_id;
SQL;

        $result = $this->connection->executeQuery($sql)->fetchAll();

        return $this->formatUserAndFamilyIds($result);
    }

    /**
     * Returns the family IDS of unclassified products. As a result, all those
     * IDs are associated with all the users IDs, as all users own unclassified
     * products.
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function getUserAndFamilyIdsForUnclassifiedProducts(): array
    {
//        $sql = <<<SQL
        //SQL;
//
//        $results = $this->connection->executeQuery($sql)->fetchAll();

        return $this->formatUserAndFamilyIds([]);
    }

    /**
     * Decodes the JSON content of the queries result.
     *
     * @param array $userAndFamilyIds
     *
     * @return array
     */
    private function formatUserAndFamilyIds(array $userAndFamilyIds): array
    {
        $formattedIds = array_map(function (array $result) {
            return [
                'user_id' => (int) $result['user_id'],
                'family_ids' => json_decode($result['family_ids'], true),
            ];
        }, $userAndFamilyIds);

        return $formattedIds;
    }
}
