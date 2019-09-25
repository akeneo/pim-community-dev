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
     * - else, attributes of the family without family variant attributes and family variant axes
     */
    public function fetchByProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH family_attributes AS (
    SELECT
        product_model.code AS code,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_family_variant family_variant ON family_variant.id = product_model.family_variant_id
    INNER JOIN pim_catalog_family family ON family.id = family_variant.family_id
    INNER JOIN pim_catalog_family_attribute family_attributes ON family_attributes.family_id = family.id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = family_attributes.attribute_id
    WHERE product_model.parent_id IS NOT NULL
    AND product_model.code IN (:productModelCodes)
    GROUP BY code
),
family_variant_attributes AS (
    SELECT
        product_model.code AS code,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_model.family_variant_id = variant_set.family_variant_id
    INNER JOIN pim_catalog_variant_attribute_set_has_attributes variant_attributes ON variant_attributes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_attributes.attributes_id
    WHERE product_model.parent_id IS NOT NULL
    AND product_model.code IN (:productModelCodes)
    GROUP BY code
),
family_variant_axes AS (
    SELECT
        product_model.code AS code,
        JSON_ARRAYAGG(attribute.code) AS attribute_codes
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_model.family_variant_id = variant_set.family_variant_id
    INNER JOIN pim_catalog_variant_attribute_set_has_axes variant_axes ON variant_axes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_axes.axes_id
    WHERE product_model.parent_id IS NOT NULL
    AND product_model.code IN (:productModelCodes)
    GROUP BY code
)
SELECT
    family_attributes.code AS code,
    family_attributes.attribute_codes AS family_attribute_codes,
    COALESCE(family_variant_attributes.attribute_codes, '[]') AS variant_attributes,
    COALESCE(family_variant_axes.attribute_codes, '[]') AS variant_axes
FROM family_attributes
LEFT JOIN family_variant_attributes ON family_variant_attributes.code = family_attributes.code
LEFT JOIN family_variant_axes ON family_variant_axes.code = family_attributes.code
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        foreach ($productModelCodes as $productModelCode) {
            $results[$productModelCode]['ancestor_attribute_codes'] = [];
        }
        foreach ($rows as $row) {
            $familyAttributes = json_decode($row['family_attribute_codes']);
            $variantAttributes = json_decode($row['variant_attributes']);
            $variantAxes = json_decode($row['variant_axes']);
            $ancestorAttributeCodes = array_diff($familyAttributes, $variantAttributes, $variantAxes);
            sort($ancestorAttributeCodes);

            $results[$row['code']] = [
                'ancestor_attribute_codes' => $ancestorAttributeCodes,
            ];
        }

        return $results;
    }
}
