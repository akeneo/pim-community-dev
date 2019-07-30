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
 * The data returned are formatted as "family_code" => ["user_ids"]:
 * [
 *     'fridges' => [1, 42],
 *     'scanners' => [2, 42, 7],
 * ].
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectUsersAbleToCompleteFamiliesMissingMappingQuery
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
SELECT family_code, JSON_ARRAYAGG(user_id) AS user_ids
FROM (
    SELECT DISTINCT uag.user_id, f.code AS family_code
    FROM pimee_franklin_insights_subscription s
        INNER JOIN pim_catalog_product p ON p.id = s.product_id AND p.family_id IS NOT NULL
        INNER JOIN pim_catalog_family f on f.id = p.family_id
        CROSS JOIN oro_user u
        LEFT JOIN oro_user_access_group uag ON uag.user_id = u.id
        LEFT JOIN pim_catalog_category_product cp ON cp.product_id = p.id
        LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id = uag.group_id
    WHERE s.misses_mapping IS TRUE
        AND (cp.category_id IS NULL OR pca.own_items IS TRUE)
) user_family
GROUP BY family_code;
SQL;

        $results = $this->connection->executeQuery($sql)->fetchAll();

        $formattedUserIdsAndFamilyCodes = [];
        foreach ($results as $usersPerFamily) {
            $formattedUserIdsAndFamilyCodes[$usersPerFamily['family_code']] = json_decode($usersPerFamily['user_ids'], true);
        }

        return $formattedUserIdsAndFamilyCodes;
    }
}
