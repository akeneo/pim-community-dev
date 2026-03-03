<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelAssociationsByProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * It generates product models associations with every association types, even if there is no product model associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at product level.
     *
     * @return array ['uuidProductA' => ['X_SELL' => ['productModel1'], 'UPSELL' => []]]
     */
    public function fetchByProductUuids(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        Assert::allIsInstanceOf($productUuids, UuidInterface::class);

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $query = <<<SQL
SELECT
    /*+ SET_VAR(sort_buffer_size = 1000000) */
    BIN_TO_UUID(product_uuid) AS uuid,
    JSON_OBJECTAGG(association_type_code, product_model_associations_by_type) as associations
FROM (
         SELECT product_uuid,
                association_type_code,
                JSON_ARRAYAGG(associated_product_model_code) as product_model_associations_by_type
         FROM (
                  SELECT product.uuid as product_uuid,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           CROSS JOIN pim_catalog_association_type association_type
                           LEFT JOIN pim_catalog_association association ON association.owner_uuid = product.uuid AND association_type.id = association.association_type_id
                           LEFT JOIN pim_catalog_association_product_model product_model_association ON association.id = product_model_association.association_id
                           LEFT JOIN pim_catalog_product_model associated_product_model ON product_model_association.product_model_id = associated_product_model.id
                  WHERE product.uuid IN (:productUuids)
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT product.uuid as product_uuid,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model product_model ON product.product_model_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                           INNER JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE product.uuid IN (:productUuids)
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT product.uuid as product_uuid,
                         association_type.code as association_type_code,
                         associated_product_model.code as associated_product_model_code
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model child_product_model ON product.product_model_id = child_product_model.id
                           INNER JOIN pim_catalog_product_model product_model ON child_product_model.parent_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_product_model product_model_to_product_model ON product_model_association.id = product_model_to_product_model.association_id
                           INNER JOIN pim_catalog_product_model associated_product_model ON product_model_to_product_model.product_model_id = associated_product_model.id
                  WHERE product.uuid IN (:productUuids)
                  AND association_type.is_quantified = false
              ) all_product_model_associations
         GROUP BY all_product_model_associations.product_uuid, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_uuid
SQL;

        $rows = $this->connection->executeQuery(
            $query,
            ['productUuids' => $uuidsAsBytes],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $results = [];

        foreach ($rows as $row) {
            $associations = json_decode($row['associations'], true);

            $filteredAssociations = [];
            foreach ($associations as $associationType => $productAssociations) {
                $association = array_values(array_filter($productAssociations));
                sort($association);
                $filteredAssociations[$associationType]['product_models'] = $association;
            }

            $results[$row['uuid']] = $filteredAssociations;
        }

        return $results;
    }
}
