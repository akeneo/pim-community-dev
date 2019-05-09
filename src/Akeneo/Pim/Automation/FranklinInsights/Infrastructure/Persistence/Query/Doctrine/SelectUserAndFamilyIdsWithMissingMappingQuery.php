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
        $sql = <<<SQL
SELECT user_id, JSON_ARRAYAGG(family_id) AS family_ids
FROM (
    SELECT DISTINCT uag.user_id, p.family_id
    FROM pimee_franklin_insights_subscription s
        INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
        CROSS JOIN oro_user u
        LEFT JOIN oro_user_access_group uag ON uag.user_id = u.id
        LEFT JOIN pim_catalog_category_product cp ON cp.product_id = p.id
        LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id = uag.group_id
    WHERE s.misses_mapping IS TRUE
        AND (cp.category_id IS NULL OR pca.own_items IS TRUE)
) user_family
GROUP BY user_id;
SQL;

        $result = $this->connection->executeQuery($sql)->fetchAll();

        $formattedIds = [];
        foreach ($result as $familyIdsPerUser) {
            $formattedIds[$familyIdsPerUser['user_id']] = json_decode($familyIdsPerUser['family_ids'], true);
        }

        return $formattedIds;
    }
}
