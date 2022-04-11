<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * SQL Query to get the properties and the values from a set of product identifiers:
 * identifier, is_enabled, product_model_code, created, updated, family_code, group_codes and raw_data.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetValuesAndPropertiesFromProductIdentifiers
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByProductIdentifiers(array $productIdentifiers): array
    {
        $query = <<<SQL
WITH groupCodes AS (
    SELECT p.uuid AS puuid, JSON_ARRAYAGG(g.code) AS group_codes
    FROM pim_catalog_product p
    LEFT JOIN pim_catalog_group_product pg ON p.uuid = pg.product_uuid
    LEFT JOIN pim_catalog_group g ON pg.group_id = g.id
    WHERE p.identifier IN (?)
    GROUP BY p.uuid
)
SELECT
    BIN_TO_UUID(p.uuid) AS uuid,
    p.identifier,
    p.is_enabled,
    pm1.code AS product_model_code,
    p.created,
    p.updated,
    f.code AS family_code,
    group_codes,
    JSON_MERGE(COALESCE(pm1.raw_values, '{}'), COALESCE(pm2.raw_values, '{}'), p.raw_values) as raw_values
FROM pim_catalog_product p
LEFT JOIN pim_catalog_family f ON p.family_id = f.id
LEFT JOIN pim_catalog_product_model pm1 ON p.product_model_id = pm1.id
LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
INNER JOIN groupCodes gc ON p.uuid = gc.puuid
WHERE p.identifier IN (?)
GROUP BY p.uuid, p.identifier
SQL;

        $rows = $this->connection->fetchAllAssociative(
            $query,
            [$productIdentifiers, $productIdentifiers],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        $platform = $this->connection->getDatabasePlatform();
        $results = [];
        foreach ($rows as $row) {
            $groupCodes = array_values(array_filter(json_decode($row['group_codes'])));
            sort($groupCodes);

            $results[$row['identifier']] = [
                'uuid' => Uuid::fromString(Type::getType(Types::STRING)->convertToPHPValue($row['uuid'], $platform)),
                'identifier' => Type::getType(Types::STRING)->convertToPHPValue($row['identifier'], $platform),
                'is_enabled' => Type::getType(Types::BOOLEAN)->convertToPHPValue($row['is_enabled'], $platform),
                'product_model_code' => Type::getType(Types::STRING)->convertToPHPValue($row['product_model_code'], $platform),
                'created' => Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['created'], $platform),
                'updated' => Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['updated'], $platform),
                'family_code' => Type::getType(Types::STRING)->convertToPHPValue($row['family_code'], $platform),
                'group_codes' => $groupCodes,
                'raw_values' => json_decode($row['raw_values'], true)
            ];
        }

        return $results;
    }
}
