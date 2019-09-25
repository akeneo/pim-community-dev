<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetAttributesFromProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Ancestor attribute codes =
     * - if there is no parent, []
     * - else, common attributes (i.e. attributes of the family without family variant attributes and family variant axes)
     *
     * Attributes for this level =
     * - if product_model level = 0 (no parent), return common attributes (see above)
     * - if product_model level = 1 (with a parent), return family variant attributes of level 1.
     */
    public function fetchByProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH family_attributes AS (
    SELECT
        product_family.id AS family_id,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM (
        SELECT DISTINCT(family_variant.family_id) AS id
        FROM pim_catalog_family_variant family_variant
        INNER JOIN pim_catalog_product_model product_model ON product_model.family_variant_id = family_variant.id
        WHERE product_model.code IN (:productModelCodes)
    ) AS product_family
    INNER JOIN pim_catalog_family_attribute family_attributes ON family_attributes.family_id = product_family.id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = family_attributes.attribute_id
    GROUP BY family_id
),
family_variant_attributes AS (
    SELECT
        product_family_variant.id AS family_variant_id,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM (
        SELECT DISTINCT(product_model.family_variant_id) AS id
        FROM pim_catalog_product_model product_model
        WHERE product_model.code IN (:productModelCodes)
    ) AS product_family_variant
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_family_variant.id = variant_set.family_variant_id
    INNER JOIN pim_catalog_variant_attribute_set_has_attributes variant_attributes ON variant_attributes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_attributes.attributes_id
    GROUP BY family_variant_id
),
family_variant_axes AS (
    SELECT
        product_model.code AS code,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_model.family_variant_id = variant_set.family_variant_id
    INNER JOIN pim_catalog_variant_attribute_set_has_axes variant_axes ON variant_axes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_axes.axes_id
    WHERE product_model.code IN (:productModelCodes)
    GROUP BY code
),
family_variant_attributes_for_sub_product_models AS (
    SELECT
        product_model.code AS code,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_model.family_variant_id = variant_set.family_variant_id
    INNER JOIN pim_catalog_family_variant_attribute_set attribute_set ON attribute_set.id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_variant_attribute_set_has_attributes variant_attributes ON variant_attributes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_attributes.attributes_id
    WHERE product_model.parent_id IS NOT NULL
    AND attribute_set.level = 1
    AND product_model.code IN (:productModelCodes)
    GROUP BY code
)
SELECT
    product_model.code AS code,
    product_model.parent_id AS parent_id,
    family_attributes.attribute_codes AS family_attribute_codes,
    COALESCE(family_variant_attributes.attribute_codes, '[]') AS variant_attributes,
    COALESCE(family_variant_axes.attribute_codes, '[]') AS variant_axes,
    COALESCE(family_variant_attributes_for_sub_product_models.attribute_codes, '[]') AS attributes_for_this_level
FROM pim_catalog_product_model product_model
INNER JOIN pim_catalog_family_variant family_variant ON family_variant.id = product_model.family_variant_id
INNER JOIN family_attributes ON family_attributes.family_id = family_variant.family_id
LEFT JOIN family_variant_attributes ON family_variant_attributes.family_variant_id = product_model.family_variant_id
LEFT JOIN family_variant_axes ON family_variant_axes.code = product_model.code
LEFT JOIN family_variant_attributes_for_sub_product_models ON family_variant_attributes_for_sub_product_models.code = product_model.code
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = array_fill_keys(
            $productModelCodes,
            [
                'ancestor_attribute_codes' => [],
                'attributes_for_this_level' => [],
            ]
        );

        foreach ($rows as $row) {
            $familyAttributes = json_decode($row['family_attribute_codes']);
            $variantAttributes = json_decode($row['variant_attributes']);
            $variantAxes = json_decode($row['variant_axes']);
            $attributesForThisLevel = json_decode($row['attributes_for_this_level']);
            $commonAttributes = array_diff($familyAttributes, $variantAttributes, $variantAxes);

            $results[$row['code']] = [
                'ancestor_attribute_codes' => null === $row['parent_id'] ? [] : array_values($commonAttributes),
                'attributes_for_this_level' => null === $row['parent_id'] ? array_values($commonAttributes) : $attributesForThisLevel,
            ];
        }

        return $results;
    }
}
