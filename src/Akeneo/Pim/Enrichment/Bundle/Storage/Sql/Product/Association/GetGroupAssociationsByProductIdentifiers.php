<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Doctrine\DBAL\Connection;

/**
 * Get groups associated to products by identifiers
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetGroupAssociationsByProductIdentifiers
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * It generates product associations with every association type, even if there is no group associated for this association type.
     * That's why it uses a CROSS JOIN when getting associations at product level.
     *
     * @return array ['productA' => ['assocType1' => ['groupA'], 'assocType2' => []]]
     */
    public function fetchByProductIdentifier(array $productIdentifiers): array
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
    JSON_OBJECTAGG(association_type_code, group_associations_by_type) as associations
FROM (
         SELECT product_identifier,
                association_type_code,
                JSON_ARRAYAGG(associated_group_identifier) as group_associations_by_type
         FROM (
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_group.code as associated_group_identifier
                  FROM pim_catalog_product product
                           CROSS JOIN pim_catalog_association_type association_type
                           LEFT JOIN pim_catalog_association association ON association.owner_id = product.id AND association_type.id = association.association_type_id
                           LEFT JOIN pim_catalog_association_group group_association ON association.id = group_association.association_id
                           LEFT JOIN pim_catalog_group associated_group ON group_association.group_id = associated_group.id
                  WHERE product.identifier IN (?)
                  UNION ALL
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_group.code as associated_group_identifier
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model product_model ON product.product_model_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_group product_model_to_group ON product_model_association.id = product_model_to_group.association_id
                           INNER JOIN pim_catalog_group associated_group ON product_model_to_group.group_id = associated_group.id
                  WHERE product.identifier IN (?)
                  UNION ALL
                  SELECT product.identifier    as product_identifier,
                         association_type.code as association_type_code,
                         associated_group.code as associated_group_identifier
                  FROM pim_catalog_product product
                           INNER JOIN pim_catalog_product_model child_product_model ON product.product_model_id = child_product_model.id
                           INNER JOIN pim_catalog_product_model product_model ON child_product_model.parent_id = product_model.id
                           INNER JOIN pim_catalog_product_model_association product_model_association ON product_model.id = product_model_association.owner_id
                           INNER JOIN pim_catalog_association_type association_type ON product_model_association.association_type_id = association_type.id
                           INNER JOIN pim_catalog_association_product_model_to_group product_model_to_group ON product_model_association.id = product_model_to_group.association_id
                           INNER JOIN pim_catalog_group associated_group ON product_model_to_group.group_id = associated_group.id
                  WHERE product.identifier IN (?)
              ) all_group_associations
         GROUP BY all_group_associations.product_identifier, association_type_code
     ) result_by_identifier_and_type
GROUP BY result_by_identifier_and_type.product_identifier
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            [$productIdentifiers, $productIdentifiers, $productIdentifiers],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        foreach ($rows as $row) {
            $associations = json_decode($row['associations'], true);

            $filteredAssociations = [];
            foreach ($associations as $associationType => $productAssociations) {
                $association = array_values(array_filter($productAssociations));
                sort($association);
                $filteredAssociations[$associationType]['groups'] = $association;
            }

            $results[$row['product_identifier']] = $filteredAssociations;
        }

        return $results;
    }
}
