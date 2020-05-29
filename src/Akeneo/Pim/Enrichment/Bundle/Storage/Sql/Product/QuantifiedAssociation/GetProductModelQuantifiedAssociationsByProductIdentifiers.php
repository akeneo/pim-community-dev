<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Doctrine\DBAL\Connection;

final class GetProductModelQuantifiedAssociationsByProductIdentifiers
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
     *      'productA' => [
     *          'PACK' => [
     *              'product_models' => [
     *                  ['identified' => 'productModelA','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if ([] === $productIdentifiers) {
            return [];
        }

        $productIdentifiers = (function (string ...$identifiers) {
            return $identifiers;
        })(... $productIdentifiers);

        $query = <<<SQL
select
    p.identifier product_identifier,
    JSON_UNQUOTE(
       JSON_EXTRACT(
           JSON_KEYS(p.quantified_associations),
           CONCAT("$[", association_type_ordinality - 1, "]")
	   )
    ) association_type_code,
    existing_pm.code associated_product_model_code,
    associated_product_model_quantity
from pim_catalog_product p,
json_table (
    p.quantified_associations,
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
WHERE p.identifier IN (:productIdentifiers)
AND JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(p.quantified_associations), CONCAT("$[", association_type_ordinality - 1, "]"))) IN (
    SELECT code
    FROM pim_catalog_association_type
)
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $results = [];
        foreach ($rows as $row) {
            $associationTypeCode = $row['association_type_code'];
            $productIdentifier = $row['product_identifier'];
            $results[$productIdentifier][$associationTypeCode]['product_models'][] = [
                'identifier' => $row['associated_product_model_code'],
                'quantity' => (int)$row['associated_product_model_quantity'],
            ];
        }

        return $results;
    }
}
