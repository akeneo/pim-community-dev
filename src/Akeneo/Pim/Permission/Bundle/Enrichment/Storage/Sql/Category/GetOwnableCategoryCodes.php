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

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class GetOwnableCategoryCodes implements GetGrantedCategoryCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        p1_.own_items = 1
        AND p1_.user_group_id IN (?)
) as ownable_category 
JOIN pim_catalog_category category ON category.id = ownable_category.category_id;
SQL;

        $categoryCodes = $this->connection->fetchAll($query, [$groupIds], [Connection::PARAM_INT_ARRAY]);

        return array_map(function ($categoryCode) : string {
            return $categoryCode['category_code'];
        }, $categoryCodes);
    }
}
