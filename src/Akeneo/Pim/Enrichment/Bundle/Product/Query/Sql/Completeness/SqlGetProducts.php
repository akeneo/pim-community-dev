<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProducts;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\Product;
use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetProducts implements \Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProducts
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return Product[]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
SELECT
    product.id AS id,
    product.raw_values AS rawValues,
    family.code AS familyCode,
    product.identifier AS identifier
FROM pim_catalog_product product
INNER JOIN pim_catalog_family family ON product.family_id=family.id
WHERE product.identifier IN (:productIdentifiers)
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = new Product(
                intval($row['id']),
                $row['identifier'],
                $row['familyCode'],
                json_decode($row['rawValues'], true)
            );
        }

        return $result;
    }
}
