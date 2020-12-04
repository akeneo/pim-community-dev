<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeGroup\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributeCodesForAttributeGroup
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $attributeGroupCode): array
    {
        $query = <<<SQL
SELECT a.code
FROM pim_catalog_attribute_group g INNER JOIN pim_catalog_attribute a ON g.id = a.group_id WHERE g.code=:group_code;
SQL;

        return $this->connection
            ->executeQuery($query, [
                'group_code' => $attributeGroupCode
            ])
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
