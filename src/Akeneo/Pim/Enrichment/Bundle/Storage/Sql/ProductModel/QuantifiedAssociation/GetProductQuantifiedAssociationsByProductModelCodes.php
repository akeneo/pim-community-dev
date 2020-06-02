<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Doctrine\DBAL\Connection;

final class GetProductQuantifiedAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Executes SQL query to get product quantified associations from a set of product model codes.
     * Returns an array like:
     * [
     *      'productModelA' => [
     *          'PACK' => [
     *              'products' => [
     *                  ['identified' => 'productA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $query = <<<SQL
select
    pm.code product_model_code,
    JSON_UNQUOTE(
       JSON_EXTRACT(
           JSON_KEYS(pm.quantified_associations),
           CONCAT("$[", association_type_ordinality - 1, "]")
	   )
    ) association_type_code,
    existing_p.identifier associated_product_identifier,
    associated_product_quantity
from pim_catalog_product_model pm,
json_table (
    pm.quantified_associations,
    '$.*'
    columns (
	association_type_ordinality FOR ORDINALITY,
        nested path '$.products[*]'
        columns (
	        `associated_product_id` VARCHAR(255) PATH '$.id',
            `associated_product_quantity` VARCHAR(255) PATH '$.quantity'
        )
    )
) quantified_associations_extracted
INNER JOIN pim_catalog_product existing_p ON quantified_associations_extracted.associated_product_id = existing_p.id
WHERE pm.code IN (:productModelCodes)
AND JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(pm.quantified_associations), CONCAT("$[", association_type_ordinality - 1, "]"))) IN (
    SELECT code
    FROM pim_catalog_association_type
)
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $associationTypeCode = $row['association_type_code'];
            $productModelCode = $row['product_model_code'];
            $results[$productModelCode][$associationTypeCode]['products'][] = [
                'identifier' => $row['associated_product_identifier'],
                'quantity' => (int)$row['associated_product_quantity'],
            ];
        }

        return $results;
    }
}
