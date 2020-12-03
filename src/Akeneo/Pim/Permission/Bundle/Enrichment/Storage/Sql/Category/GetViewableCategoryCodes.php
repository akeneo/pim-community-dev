<?php
declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Product\Component\Product\Query\GetViewableCategoryCodes as GetViewableCategoryCodesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Given a list of category codes, get viewable category codes
 *
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetViewableCategoryCodes implements GetViewableCategoryCodesInterface
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

    public function forCategoryCodes(int $userId, array $categoryCodes): array
    {
        if ($categoryCodes === []) {
            return [];
        }

        $query = <<<SQL
SELECT category.code as category_code
FROM pim_catalog_category category
WHERE EXISTS (
        SELECT *
        FROM pimee_security_product_category_access category_access
        JOIN oro_user_access_group group_access ON group_access.group_id = category_access.user_group_id
        WHERE group_access.user_id = (?) AND category_access.view_items = 1 AND category_access.category_id = category.id
    )
  AND category.code IN (?)

SQL;

        $results = $this->connection->fetchAll(
            $query,
            [$userId, $categoryCodes],
            [ParameterType::INTEGER, Connection::PARAM_STR_ARRAY]
        );

        return array_map(function ($result) {
            return $result['category_code'];
        }, $results);
    }

    /**
     * This query use a subselect to compute the distinct directly on the ids of the categories. This is done for performance reason.
     */
    public function forGroupIds(array $groupIds): array
    {
        $query = <<<SQL
SELECT category.code as category_code
FROM 
(
    SELECT 
        DISTINCT p1_.category_id 
    FROM 
        pimee_security_product_category_access p1_ 
    WHERE 
        p1_.view_items = 1
        AND p1_.user_group_id IN (?)
) as viewable_category 
JOIN pim_catalog_category category ON category.id = viewable_category.category_id;
SQL;

        $categoryCodes = $this->connection->fetchAll($query, [$groupIds], [Connection::PARAM_INT_ARRAY]);

        return array_map(function ($categoryCode) : string {
            return $categoryCode['category_code'];
        }, $categoryCodes);
    }
}
