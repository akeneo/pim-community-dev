<?php


namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsFromFamily
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByFamilyCode(string $familyCode): array
    {
        $query =<<<SQL
select product.identifier from pim_catalog_product product
    INNER JOIN pim_catalog_family family on product.family_id = family.id
        WHERE  family.code = ?
SQL;
        return $this->connection->executeQuery(
            $query,
            [[$familyCode]],
            [Connection::PARAM_STR_ARRAY]
        )->fetchAll(FetchMode::COLUMN);
    }

}
