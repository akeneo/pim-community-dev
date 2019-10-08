<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\Connection;

/**
 * Get product model code associated to product models by code
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelsAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * It generates product models associations with every association types, even if there is no product model associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at first product model level.
     *
     * @return array ['productModelA' => ['X_SELL' => ['productModel1'], 'UPSELL' => []]]
     */
    public function fromProductModelCodes(array $productModelCodes): array
    {
        if ([] === $productModelCodes) {
            return [];
        }

        $productModelCodes = (function (string ...$codes) {
            return $codes;
        })(... $productModelCodes);

        $query = <<<SQL
SELECT
    product_model_code,
    JSON_OBJECTAGG(association_type_code, product_model_associations_by_type) as associations
FROM (
         SELECT product_model_code,
                association_type_code,
                JSON_ARRAYAGG(associated_product_model_code) as product_model_associations_by_type
         FROM (
                  SELECT product_model.code    as product_model_code,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product_model product_model
                  CROSS JOIN pim_catalog_association_type association_type
                  LEFT JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id AND association_type.id = product_model_association.association_type_id
                  LEFT JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                  LEFT JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE product_model.code IN (:productModelCodes)
                  UNION ALL
                  SELECT child_product_model.code    as product_model_code,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product_model child_product_model
                  INNER JOIN pim_catalog_product_model product_model ON child_product_model.parent_id = product_model.id
                  INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                  INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                  INNER JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                  INNER JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE child_product_model.code IN (:productModelCodes)
              ) all_product_model_associations
         GROUP BY all_product_model_associations.product_model_code, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_model_code
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
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
            $results[$row['product_model_code']] = $filteredAssociations;
        }

        return $results;
    }
}
