<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelAssociationsByProductIdentifiers
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * It generates product models associations with every association types, even if there is no product model associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at product level.
     *
     * @return array ['productA' => ['X_SELL' => ['productModel1'], 'UPSELL' => []]]
     */
    public function fetchByProductIdentifiers(array $productIdentifiers): array
    {
        if ([] === $productIdentifiers) {
            return [];
        }

        $productIdentifiers = (function (string ...$identifiers) {
            return $identifiers;
        })(... $productIdentifiers);

        $query = <<<SQL
SELECT
    product_identifier,
    JSON_OBJECTAGG(association_type_code, product_model_associations_by_type) as associations
FROM (
         SELECT product_identifier,
                association_type_code,
                JSON_ARRAYAGG(associated_product_model_code) as product_model_associations_by_type
         FROM (
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           CROSS JOIN pim_catalog_association_type association_type
                           LEFT JOIN pim_catalog_association association ON association.owner_id = product.id AND association_type.id = association.association_type_id
                           LEFT JOIN pim_catalog_association_product_model product_model_association ON association.id = product_model_association.association_id
                           LEFT JOIN pim_catalog_product_model associated_product_model ON product_model_association.product_model_id = associated_product_model.id
                  WHERE product.identifier IN (:productIdentifiers)
                  UNION ALL
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model product_model ON product.product_model_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                           INNER JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE product.identifier IN (:productIdentifiers)
                  UNION ALL
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model child_product_model ON product.product_model_id = child_product_model.id
                           INNER JOIN pim_catalog_product_model product_model ON child_product_model.parent_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                           INNER JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE product.identifier IN (:productIdentifiers)
              ) all_product_model_associations
         GROUP BY all_product_model_associations.product_identifier, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_identifier
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productIdentifiers' => $productIdentifiers],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
            )->fetchAll();

        $results = [];

        foreach ($rows as $row) {
            $associations = json_decode($row['associations'], true);

            $filteredAssociations = [];
            foreach ($associations as $associationType => $productAssociations) {
                $association = array_values(array_filter($productAssociations));
                sort($association);
                $filteredAssociations[$associationType]['product_models'] = $association;
            }

            $results[$row['product_identifier']] = $filteredAssociations;
        }

        return $results;
    }
}
