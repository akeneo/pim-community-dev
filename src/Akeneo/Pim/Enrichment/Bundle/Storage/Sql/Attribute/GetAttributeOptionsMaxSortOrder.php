<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute;

use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeOptionsMaxSortOrder
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAttributeCodes(array $attributeCodes): array
    {
        $sql = <<<SQL
SELECT a.code AS attribute_code, MAX(o.sort_order) as sort_order
FROM pim_catalog_attribute_option o
INNER JOIN pim_catalog_attribute a ON a.id = o.attribute_id
WHERE a.code IN (:attributeCodes)
GROUP BY attribute_code;
SQL;

        $sortOrders = [];
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'attributeCodes' => $attributeCodes,
            ],
            [
                'attributeCodes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll();

        foreach ($rows as $row) {
            $sortOrders[$row['attribute_code']] = (int) $row['sort_order'];
        }

        return $sortOrders;
    }
}
