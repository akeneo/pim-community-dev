<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributeCodeAsLabelForFamily implements FindAttributeCodeAsLabelForFamilyInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $code): ?string
    {
        $sql = <<<SQL
        SELECT a.code
        FROM pim_catalog_family f
          INNER JOIN pim_catalog_attribute a ON f.label_attribute_id = a.id
        WHERE (f.code = :code)
SQL;
        $result = $this->connection->executeQuery($sql, ['code' => $code])->fetch(\PDO::FETCH_COLUMN);

        if (!is_string($result)) {
            return null;
        }

        return $result;
    }
}
