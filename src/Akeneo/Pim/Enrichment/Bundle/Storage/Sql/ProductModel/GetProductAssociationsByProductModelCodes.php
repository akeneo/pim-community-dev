<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel;

use Doctrine\DBAL\Connection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductAssociationsByProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByProductModelCodes(array $productModelCodes): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $productModelCodes = (function (string ...$codes) {
            return $codes;
        })(... $productModelCodes);

        $query = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT
    /*+ SET_VAR(sort_buffer_size = 1000000) */
    product_model_code,
    JSON_OBJECTAGG(association_type_code, associations_by_type) as associations
FROM (
         SELECT
product_model_code,
association_type_code,
JSON_ARRAYAGG(associated_product_identifier) as associations_by_type
         FROM (
                  SELECT
                      product_model.code as product_model_code,
                      association_type.code as association_type_code,
                      pcpud.raw_data as associated_product_identifier
                  FROM pim_catalog_product_model product_model
                           CROSS JOIN pim_catalog_association_type association_type
                           LEFT JOIN pim_catalog_product_model_association product_model_association ON product_model_association.owner_id = product_model.id AND association_type.id = product_model_association.association_type_id
                           LEFT JOIN pim_catalog_association_product_model_to_product association_to_product_model ON association_to_product_model.association_id = product_model_association.id
                           LEFT JOIN pim_catalog_product_unique_data pcpud ON pcpud.product_uuid = association_to_product_model.product_uuid AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                  WHERE product_model.code IN (:productModelCodes)
                  AND association_type.is_quantified = false
                  UNION DISTINCT 
                  SELECT
                      child_product_model.code as product_model_code,
                      association_type.code as association_type_code,
                      pcpud.raw_data as associated_product_identifier
                  FROM pim_catalog_product_model child_product_model
                       INNER JOIN pim_catalog_product_model root_product_model ON child_product_model.parent_id = root_product_model.id
                       INNER JOIN pim_catalog_product_model_association product_model_association ON root_product_model.id = product_model_association.owner_id
                       INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                       INNER JOIN pim_catalog_association_product_model_to_product product_model_to_product ON product_model_association.id = product_model_to_product.association_id
                       LEFT JOIN pim_catalog_product_unique_data pcpud ON pcpud.product_uuid = product_model_to_product.product_uuid AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                  WHERE child_product_model.code IN (:productModelCodes)
                  AND association_type.is_quantified = false
              ) all_associations
         GROUP BY all_associations.product_model_code, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_model_code
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];

        foreach ($rows as $row) {
            $associations = json_decode($row['associations'], true);
            $filteredAssociations = [];
            foreach ($associations as $associationType => $productAssociations) {
                $association = array_values(array_filter($productAssociations));
                sort($association);
                $filteredAssociations[$associationType]['products'] = $association;
            }
            $results[$row['product_model_code']] = $filteredAssociations;
        }

        return $results;
    }
}
