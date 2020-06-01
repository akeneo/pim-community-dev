<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation;

use Doctrine\DBAL\Connection;

final class GetProductQuantifiedAssociationsByProductIdentifiers
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
     *              'products' => [
     *                  ['identified' => 'productB','quantity' => 5]
     *              ]
     *          ]
     *      ]
     * ]
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
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
    existing_p.identifier associated_product_identifier,
    associated_product_quantity
from pim_catalog_product p,
json_table (
    p.quantified_associations,
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
            $results[$productIdentifier][$associationTypeCode]['products'][] = [
                'identifier' => $row['associated_product_identifier'],
                'quantity' => (int)$row['associated_product_quantity'],
            ];
        }

        return $results;
    }
}
