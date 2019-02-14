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
 * The data returned are formatted as "user_id" => ["family_id"]:
 * [
 *     1 => [42, 43],
 *     2 => [42, 44, 45],
 * ].
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
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function execute(): array
    {
        $idsForClassifiedProducts = $this->getUserAndFamilyIdsForClassifiedProducts();
        $idsForUnclassifiedProducts = $this->getUserAndFamilyIdsForUnclassifiedProducts();

        return $this->mergeQueryResults($idsForClassifiedProducts, $idsForUnclassifiedProducts);
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
SELECT uag.user_id AS user_id,
    JSON_ARRAYAGG(p.family_id) AS family_ids
FROM pimee_franklin_insights_subscription s
    INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
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
        $sql = <<<SQL
SELECT u.id AS user_id,
    JSON_ARRAYAGG(p.family_id) AS family_ids
FROM pimee_franklin_insights_subscription s
    INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
    INNER JOIN oro_user u
    LEFT OUTER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
WHERE s.misses_mapping IS TRUE
    AND cp.category_id IS NULL
GROUP BY u.id;
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAll();

        return $this->formatUserAndFamilyIds($results);
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
        $formattedIds = [];
        foreach ($userAndFamilyIds as $familyIdsPerUser) {
            $formattedIds[$familyIdsPerUser['user_id']] = json_decode($familyIdsPerUser['family_ids'], true);
        }

        return $formattedIds;
    }

    /**
     * Merges the results of the 2 queries, ensuring the preservation of the
     * indexes (as the user IDs are integers, PHP can mess them up) and the
     * uniqueness and order of the family IDs for each user ID.
     *
     * @param array $idsForClassifiedProducts
     * @param array $idsForUnclassifiedProducts
     *
     * @return array
     */
    private function mergeQueryResults(array $idsForClassifiedProducts, array $idsForUnclassifiedProducts): array
    {
        $mergedIds = $idsForClassifiedProducts;

        foreach ($idsForUnclassifiedProducts as $userId => $familyIds) {
            $mergedFamilyIds = array_key_exists($userId, $mergedIds)
                ? array_merge($familyIds, $mergedIds[$userId])
                : $familyIds;

            $mergedIds[$userId] = array_unique($mergedFamilyIds);
        }

        return $mergedIds;
    }
}
