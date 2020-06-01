<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\QuantifiedAssociation;

use Doctrine\DBAL\Connection;

final class GetProductModelQuantifiedAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     * [
     *      'productModelA' => [
     *          'PACK' => [
     *              'product_models' => [
     *                  ['identified' => 'productModelB','quantity' => 5]
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

        $productModelCodes = (function (string ...$productModelCode) {
            return $productModelCode;
        })(... $productModelCodes);

        $query = <<<SQL
select
    pm.code product_model_code,
    JSON_UNQUOTE(
       JSON_EXTRACT(
           JSON_KEYS(pm.quantified_associations),
           CONCAT("$[", association_type_ordinality - 1, "]")
	   )
    ) association_type_code,
    existing_pm.code associated_product_model_code,
    associated_product_model_quantity
from pim_catalog_product_model pm,
json_table (
    pm.quantified_associations,
    '$.*'
    columns (
	association_type_ordinality FOR ORDINALITY,
        nested path '$.product_models[*]'
        columns (
	        `associated_product_model_id` VARCHAR(255) PATH '$.id',
            `associated_product_model_quantity` VARCHAR(255) PATH '$.quantity'
        )
    )
) quantified_associations_extracted
INNER JOIN pim_catalog_product_model existing_pm ON quantified_associations_extracted.associated_product_model_id = existing_pm.id
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
            $results[$productModelCode][$associationTypeCode]['product_models'][] = [
                'identifier' => $row['associated_product_model_code'],
                'quantity' => (int)$row['associated_product_model_quantity'],
            ];
        }

        return $results;
    }
}
