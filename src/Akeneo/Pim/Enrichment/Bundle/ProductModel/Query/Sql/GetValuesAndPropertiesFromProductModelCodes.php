<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * SQL Query to get the properties and the values from a set of product model codes:
 * code, family_variant, parent, raw_values, created and updated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetValuesAndPropertiesFromProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        if ([] === $productModelCodes) {
            return [];
        }

        $productModelCodes = (function (string ...$productModelCodes) {
            return $productModelCodes;
        })(...$productModelCodes);

        $query = <<<SQL
SELECT
       product_model.code as 'code',
       family_variant.code as 'family_variant',
       parent_product_model.code as 'parent',
       JSON_MERGE(
           CASE parent_product_model.raw_values WHEN '[]' THEN '{}' ELSE COALESCE(parent_product_model.raw_values, '{}') END,
           CASE product_model.raw_values WHEN '[]' THEN '{}' ELSE product_model.raw_values END
       ) as raw_values,
       product_model.created as 'created',
       product_model.updated as 'updated'
FROM pim_catalog_product_model as product_model
INNER JOIN pim_catalog_family_variant family_variant ON product_model.family_variant_id = family_variant.id
LEFT JOIN pim_catalog_product_model parent_product_model ON parent_product_model.id = product_model.parent_id
WHERE product_model.code IN (:productModelCodes)
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $platform = $this->connection->getDatabasePlatform();
        $results = [];
        foreach ($rows as $row) {
            $results[$row['code']] = [
                'code' => Type::getType(Type::STRING)->convertToPHPValue($row['code'], $platform),
                'family_variant' => Type::getType(Type::STRING)->convertToPHPValue($row['family_variant'], $platform),
                'parent' => Type::getType(Type::STRING)->convertToPHPValue($row['parent'], $platform),
                'raw_values' => json_decode($row['raw_values'], true),
                'created' => Type::getType(Type::DATETIME)->convertToPhpValue($row['created'], $platform),
                'updated' => Type::getType(Type::DATETIME)->convertToPhpValue($row['updated'], $platform),
            ];
        }

        return $results;
    }
}
