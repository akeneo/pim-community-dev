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
 * This query returns a list of family codes corresponding to attribute mappings
 * having pending attributes.
 * Those family codes are grouped by user IDs, those of the users owning products
 * of those families, and so being able to complete the corresponding attribute
 * mappings.
 * The data returned are formatted as "user_id" => ["family_code"]:
 * [
 *     1 => ['family_1', 'family_2'],
 *     2 => ['family_1', 'family_3', 'family_4'],
 * ].
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectUserIdsAndFamilyCodesWithMissingMappingQuery
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
        $idsForClassifiedProducts = $this->getUserIdsAndFamilyCodesForClassifiedProducts();
        $idsForUnclassifiedProducts = $this->getUserIdsAndFamilyCodesForUnclassifiedProducts();

        return $this->mergeQueryResults($idsForClassifiedProducts, $idsForUnclassifiedProducts);
    }

    /**
     * Returns the family codes of classified products. This means users have own
     * permission on at least one product of each family it is associated with.
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function getUserIdsAndFamilyCodesForClassifiedProducts(): array
    {
        $sql = <<<SQL
SELECT uag.user_id AS user_id,
    JSON_ARRAYAGG(f.code) AS family_codes
FROM pimee_franklin_insights_subscription s
    INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
    INNER JOIN pim_catalog_family f on f.id = p.family_id
    INNER JOIN pim_catalog_category_product cp ON p.id = cp.product_id
    INNER JOIN pimee_security_product_category_access pca
        ON pca.category_id = cp.category_id AND pca.own_items IS TRUE
    INNER JOIN oro_user_access_group uag ON uag.group_id = pca.user_group_id
WHERE s.misses_mapping IS TRUE
GROUP BY uag.user_id;
SQL;

        $result = $this->connection->executeQuery($sql)->fetchAll();

        return $this->formatUserIdsAndFamilyCodes($result);
    }

    /**
     * Returns the family codes of unclassified products. As a result, all those
     * IDs are associated with all the users IDs, as all users own unclassified
     * products.
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function getUserIdsAndFamilyCodesForUnclassifiedProducts(): array
    {
        $sql = <<<SQL
SELECT u.id AS user_id,
    JSON_ARRAYAGG(f.code) AS family_codes
FROM pimee_franklin_insights_subscription s
    INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
    INNER JOIN pim_catalog_family f on f.id = p.family_id
    INNER JOIN oro_user u
    LEFT OUTER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
WHERE s.misses_mapping IS TRUE
    AND cp.category_id IS NULL
GROUP BY u.id;
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAll();

        return $this->formatUserIdsAndFamilyCodes($results);
    }

    /**
     * Decodes the JSON content of the queries result.
     *
     * @param array $userIdsAndFamilyCodes
     *
     * @return array
     */
    private function formatUserIdsAndFamilyCodes(array $userIdsAndFamilyCodes): array
    {
        $formattedUserIdsAndFamilyCodes = [];
        foreach ($userIdsAndFamilyCodes as $familyIdsPerUser) {
            $userId = $familyIdsPerUser['user_id'];
            $formattedUserIdsAndFamilyCodes[$userId] = json_decode($familyIdsPerUser['family_codes'], true);
        }

        return $formattedUserIdsAndFamilyCodes;
    }

    /**
     * Merges the results of the 2 queries, ensuring the preservation of the
     * indexes (as the user IDs are integers, PHP can mess them up) and the
     * uniqueness and order of the family codes for each user ID.
     *
     * @param array $resultForClassifiedProducts
     * @param array $resultForUnclassifiedProducts
     *
     * @return array
     */
    private function mergeQueryResults(array $resultForClassifiedProducts, array $resultForUnclassifiedProducts): array
    {
        $mergedResults = $resultForClassifiedProducts;

        foreach ($resultForUnclassifiedProducts as $userId => $familyCodes) {
            $mergedFamilyCodes = array_key_exists($userId, $mergedResults)
                ? array_merge($familyCodes, $mergedResults[$userId])
                : $familyCodes;

            $mergedResults[$userId] = array_unique($mergedFamilyCodes);
        }

        return $mergedResults;
    }
}
