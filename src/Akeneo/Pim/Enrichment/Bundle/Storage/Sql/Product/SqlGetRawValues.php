<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\PublicApi\GetRawValues;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetRawValues implements GetRawValues
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductIdentifiers(array $productIdentifiers): iterable
    {
        if ([] === $productIdentifiers) {
            return [];
        }
        $stmt = $this->connection->executeQuery(
            <<<SQL
        SELECT product.identifier, JSON_MERGE_PATCH(
                COALESCE(root.raw_values, '{}'),
                COALESCE(sub.raw_values, '{}'),
                product.raw_values 
            ) AS raw_values
        FROM pim_catalog_product product
            LEFT JOIN pim_catalog_product_model sub ON sub.id = product.product_model_id
            LEFT JOIN pim_catalog_product_model root ON root.id = sub.parent_id
        WHERE product.identifier IN (:identifiers)
        GROUP BY product.identifier;
        SQL,
            [
                'identifiers' => $productIdentifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        while ($row = $stmt->fetchAssociative()) {
            yield (string) $row['identifier'] => \json_decode($row['raw_values'], true);
        }
    }

    public function fromProductModelCodes(array $productModelCodes): iterable
    {
        if ([] === $productModelCodes) {
            return [];
        }
        $stmt = $this->connection->executeQuery(
            <<<SQL
            SELECT product_model.code, JSON_MERGE_PATCH(
                    COALESCE(root.raw_values, '{}'),
                    product_model.raw_values
                ) AS raw_values
            FROM pim_catalog_product_model product_model
                LEFT JOIN pim_catalog_product_model root ON root.id = product_model.parent_id
            WHERE product_model.code IN (:codes)
            GROUP BY product_model.code;
            SQL,
            ['codes' => $productModelCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        );

        while ($row = $stmt->fetchAssociative()) {
            yield (string) $row['code'] => \json_decode($row['raw_values'], true);
        }
    }
}
