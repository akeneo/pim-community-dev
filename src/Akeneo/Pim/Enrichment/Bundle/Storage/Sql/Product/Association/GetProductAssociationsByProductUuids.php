<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * Get products associated to other products by uuids
 *
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetProductAssociationsByProductUuids
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * It generates product associations with every association types, even if there is not product associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at product level.
     *
     * @param array<UuidInterface> $productUuids
     * @return array ['b445d0e8-b40c-4601-b157-0c892b7fdbb8' => ['assocType1' => ['7d702a9f-ff0d-4c7a-b071-eabfc1729312']]]
     */
    public function fetchByProductUuids(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        $query = <<<SQL
SELECT
    /*+ SET_VAR(sort_buffer_size = 1000000) */
    BIN_TO_UUID(result_by_uuid_and_type.product_uuid) as product_uuid,
    JSON_OBJECTAGG(association_type_code, associations_by_type) as associations
FROM (
         SELECT
             all_associations.product_uuid as product_uuid,
             association_type_code,
             JSON_ARRAYAGG(associated_product_uuid) as associations_by_type
         FROM (
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      associated_product.uuid as associated_product_uuid
                  FROM pim_catalog_product product
                       CROSS JOIN pim_catalog_association_type association_type
                       LEFT JOIN pim_catalog_association product_association ON product_association.owner_uuid = product.uuid AND association_type.id = product_association.association_type_id
                       LEFT JOIN pim_catalog_association_product association_to_product ON association_to_product.association_id = product_association.id
                       LEFT JOIN pim_catalog_product associated_product ON associated_product.uuid = association_to_product.product_uuid
                  WHERE product.uuid IN (:productUuids) 
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      associated_product.uuid as associated_product_uuid
                  FROM pim_catalog_product product
                       INNER JOIN pim_catalog_product_model product_model ON product_model.id = product.product_model_id
                       INNER JOIN pim_catalog_product_model_association product_model_association ON product_model_association.owner_id = product_model.id
                       INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                       INNER JOIN pim_catalog_association_product_model_to_product association_to_product ON association_to_product.association_id = product_model_association.id
                       INNER JOIN pim_catalog_product associated_product ON associated_product.uuid = association_to_product.product_uuid
                  WHERE product.uuid IN (:productUuids)
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      associated_product.uuid as associated_product_uuid
                  FROM pim_catalog_product product
                       INNER JOIN pim_catalog_product_model child_product_model ON child_product_model.id = product.product_model_id
                       INNER JOIN pim_catalog_product_model product_model ON product_model.id = child_product_model.parent_id
                       INNER JOIN pim_catalog_product_model_association product_model_association ON product_model_association.owner_id = product_model.id
                       INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                       INNER JOIN pim_catalog_association_product_model_to_product association_to_product ON association_to_product.association_id = product_model_association.id
                       INNER JOIN pim_catalog_product associated_product ON associated_product.uuid = association_to_product.product_uuid
                  WHERE product.uuid IN (:productUuids)
                  AND association_type.is_quantified = false
              ) all_associations
         GROUP BY all_associations.product_uuid, association_type_code
     ) result_by_uuid_and_type
GROUP BY result_by_uuid_and_type.product_uuid
SQL;

        $rows = $this->connection->fetchAllAssociative(
            $query,
            ['productUuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
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

            $results[$row['product_uuid']] = $filteredAssociations;
        }

        return $results;
    }
}
