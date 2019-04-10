<?php
declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Given a list of category codes, get viewable category codes
 *
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetViewableCategoryCodes
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
     * {@inheritdoc}
     */
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
}
