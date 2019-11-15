<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Storage\Sql;

use Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Doctrine\DBAL\Connection;

/**
 * Find all sort orders equals or superior to the given attribute group sort order.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindAttributeGroupOrdersEqualOrSuperiorTo implements FindAttributeGroupOrdersEqualOrSuperiorTo
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(AttributeGroup $attributeGroup): array
    {
        $sql = <<<SQL
        SELECT DISTINCT(ag.sort_order)
        FROM pim_catalog_attribute_group ag
        WHERE (ag.sort_order >= :attribute_group_order)
        AND ag.code != :attribute_group_code
        ORDER BY ag.sort_order ASC
SQL;
        $query = $this->connection->executeQuery(
            $sql,
            [
                'attribute_group_code' => $attributeGroup->getCode(),
                'attribute_group_order' => $attributeGroup->getSortOrder(),
            ]
        );

        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }
}
