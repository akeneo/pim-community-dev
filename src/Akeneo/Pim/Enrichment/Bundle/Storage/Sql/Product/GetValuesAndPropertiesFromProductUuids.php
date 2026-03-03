<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * SQL Query to get the properties and the values from a set of product identifiers:
 * identifier, is_enabled, product_model_code, created, updated, family_code, group_codes and raw_data.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetValuesAndPropertiesFromProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param array<UuidInterface> $productUuids
     */
    public function fetchByProductUuids(array $productUuids): array
    {
        $query = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
),
groupCodes AS (
    SELECT p.uuid AS puuid, JSON_ARRAYAGG(g.code) AS group_codes
    FROM pim_catalog_product p
    LEFT JOIN pim_catalog_group_product pg ON p.uuid = pg.product_uuid
    LEFT JOIN pim_catalog_group g ON pg.group_id = g.id
    WHERE p.uuid IN (?)
    GROUP BY p.uuid
)
SELECT
    BIN_TO_UUID(p.uuid) AS uuid,
    raw_data AS identifier,
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
LEFT JOIN pim_catalog_product_unique_data pcpud
    ON pcpud.product_uuid = p.uuid
    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE p.uuid IN (?)
GROUP BY p.uuid, raw_data
SQL;

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $rows = $this->connection->fetchAllAssociative(
            $query,
            [$uuidsAsBytes, $uuidsAsBytes],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        $platform = $this->connection->getDatabasePlatform();
        $results = [];
        foreach ($rows as $row) {
            $groupCodes = array_values(array_filter(json_decode($row['group_codes'])));
            sort($groupCodes);

            $results[$row['uuid']] = [
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
