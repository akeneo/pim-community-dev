<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Get products associated to other products by identifiers
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * It generates product associations with every association types, even if there is no product associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at product level.
     *
     * @return array ['uuidProductA' => ['assocType1' => [['uuid' => 'uuidAssociatedProduct', 'identifier' => 'associatedProduct' ]]]]
     */
    public function fetchByProductUuids(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        Assert::allIsInstanceOf($productUuids, UuidInterface::class);

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $query = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT
    /*+ SET_VAR(sort_buffer_size = 1000000) */
    BIN_TO_UUID(product_uuid) AS uuid,
    JSON_OBJECTAGG(association_type_code, associations_by_type) as associations
FROM (
         SELECT
             product_uuid,
             association_type_code,
             JSON_ARRAYAGG(associated_product_object) as associations_by_type
         FROM (
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      CASE 
                          WHEN association_to_product.product_uuid IS NULL THEN NULL
                          ELSE JSON_OBJECT('uuid', BIN_TO_UUID(association_to_product.product_uuid), 'identifier', pcpud.raw_data)
                      END as associated_product_object
                  FROM pim_catalog_product product
                       CROSS JOIN pim_catalog_association_type association_type
                       LEFT JOIN pim_catalog_association product_association ON product_association.owner_uuid = product.uuid AND association_type.id = product_association.association_type_id
                       LEFT JOIN pim_catalog_association_product association_to_product ON association_to_product.association_id = product_association.id
                       LEFT JOIN pim_catalog_product_unique_data pcpud ON pcpud.product_uuid = association_to_product.product_uuid AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                  WHERE product.uuid IN (?)
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      JSON_OBJECT('uuid', BIN_TO_UUID(association_to_product.product_uuid), 'identifier', pcpud.raw_data) AS associated_product_object
                  FROM pim_catalog_product product
                       INNER JOIN pim_catalog_product_model product_model ON product_model.id = product.product_model_id
                       INNER JOIN pim_catalog_product_model_association product_model_association ON product_model_association.owner_id = product_model.id
                       INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                       INNER JOIN pim_catalog_association_product_model_to_product association_to_product ON association_to_product.association_id = product_model_association.id
                       LEFT JOIN pim_catalog_product_unique_data pcpud ON pcpud.product_uuid = association_to_product.product_uuid AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                  WHERE product.uuid IN (?)
                  AND association_type.is_quantified = false
                  UNION DISTINCT
                  SELECT
                      product.uuid as product_uuid,
                      association_type.code as association_type_code,
                      JSON_OBJECT('uuid', BIN_TO_UUID(association_to_product.product_uuid), 'identifier', pcpud.raw_data) AS associated_product_object
                  FROM pim_catalog_product product
                       INNER JOIN pim_catalog_product_model child_product_model ON child_product_model.id = product.product_model_id
                       INNER JOIN pim_catalog_product_model product_model ON product_model.id = child_product_model.parent_id
                       INNER JOIN pim_catalog_product_model_association product_model_association ON product_model_association.owner_id = product_model.id
                       INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                       INNER JOIN pim_catalog_association_product_model_to_product association_to_product ON association_to_product.association_id = product_model_association.id
                       LEFT JOIN pim_catalog_product_unique_data pcpud ON pcpud.product_uuid = association_to_product.product_uuid AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                  WHERE product.uuid IN (?)
                  AND association_type.is_quantified = false
              ) all_associations
         GROUP BY all_associations.product_uuid, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_uuid
SQL;

        $rows = $this->connection->fetchAllAssociative(
            $query,
            [$uuidsAsBytes, $uuidsAsBytes, $uuidsAsBytes],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        $results = [];

        foreach ($rows as $row) {
            $associations = json_decode($row['associations'], true);

            $filteredAssociations = [];
            foreach ($associations as $associationType => $productAssociations) {
                $association = array_values(array_filter($productAssociations));
                usort($association, static fn (array $a1, array $a2): int => $a1['identifier'] <=> $a2['identifier']);
                $filteredAssociations[$associationType]['products'] = $association;
            }

            $results[$row['uuid']] = $filteredAssociations;
        }

        return $results;
    }
}
