<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ElasticsearchProjection;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetElasticsearchProductProjection implements GetElasticsearchProductProjectionInterface
{
    private const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_product_model';

    /** @var Connection */
    private $connection;

    /** @var NormalizerInterface */
    private $valuesNormalizer;

    /** @var ReadValueCollectionFactory */
    private $readValueCollectionFactory;

    /** @var GetAdditionalPropertiesForProductProjectionInterface[] */
    private $additionalDataProviders = [];

    public function __construct(
        Connection $connection,
        NormalizerInterface $valuesNormalizer,
        ReadValueCollectionFactory $readValueCollectionFactory,
        iterable $additionalDataProviders = []
    ) {
        $this->connection = $connection;
        $this->valuesNormalizer = $valuesNormalizer;
        $this->readValueCollectionFactory = $readValueCollectionFactory;
        $this->additionalDataProviders = $additionalDataProviders;
    }

    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $rows = $this->fetchRows($productIdentifiers);
        $rows = $this->calculateAttributeCodeAncestors($rows);
        $rows = $this->calculateAttributeCodeForOwnLevel($rows);

        $rowIdentifiers = array_map(function (array $row) {
            return $row['identifier'];
        }, $rows);

        $diffIdentifiers = array_diff($productIdentifiers, $rowIdentifiers);
        if (count($diffIdentifiers) > 0) {
            throw new ObjectNotFoundException(sprintf('Product identifiers "%s" were not found.', implode(',', $diffIdentifiers)));
        }

        $platform = $this->connection->getDatabasePlatform();

        $rows = $this->createValueCollectionInBatchFromRows($rows);

        $results = [];
        foreach ($rows as $row) {
            $rawValues = $row['raw_values'];

            $productLabels = [];
            if (null !== $row['attribute_as_label_code'] && isset($rawValues[$row['attribute_as_label_code']])) {
                $productLabels = $rawValues[$row['attribute_as_label_code']];
            }

            $values = $this->valuesNormalizer->normalize($row['values'], self::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX);

            // use Type::DATETIME and not Type::DATETIME_IMMUTABLE as it's overridden in the PIM (UTCDateTimeType) to handle UTC correctly
            $results[$row['identifier']] = new ElasticsearchProductProjection(
                $row['id'],
                $row['identifier'],
                \DateTimeImmutable::createFromMutable(Type::getType(Type::DATETIME)->convertToPhpValue($row['created_date'], $platform)),
                \DateTimeImmutable::createFromMutable(Type::getType(Type::DATETIME)->convertToPhpValue($row['updated_date'], $platform)),
                (bool) $row['is_enabled'],
                $row['family_code'],
                \json_decode($row['family_labels'], true),
                $row['family_variant_code'],
                \json_decode($row['category_codes'], true),
                \json_decode($row['category_codes_of_ancestors'], true),
                \json_decode($row['group_codes'], true),
                \json_decode($row['completeness'], true),
                $row['parent_product_model_code'],
                $values,
                array_filter(\json_decode($row['ancestor_ids'], true)),
                array_filter(\json_decode($row['ancestor_codes'], true)),
                $productLabels,
                $row['attribute_codes_of_ancestor'],
                $row['attribute_codes_for_this_level']
            );
        }

        foreach ($this->additionalDataProviders as $additionalDataProvider) {
            $additionalDataPerProduct = $additionalDataProvider->fromProductIdentifiers($productIdentifiers);
            foreach ($additionalDataPerProduct as $productIdentifier => $additionalData) {
                $results[$productIdentifier] = $results[$productIdentifier]->addAdditionalData($additionalData);
            }
        }

        return $results;
    }

    private function fetchRows(array $productIdentifiers): array
    {
        $sql = <<<SQL
WITH
    product as (
        SELECT
            product.id, 
            product.identifier,
            product.is_enabled,
            product.product_model_id AS parent_product_model_id,
            sub_product_model.code AS parent_product_model_code,
            family.id as family_id,
            family.code AS family_code,
            family_variant.id as family_variant_id,
            family_variant.code AS family_variant_code,
            JSON_ARRAY(sub_product_model.id, root_product_model.id) AS ancestor_ids,
            JSON_ARRAY(sub_product_model.code, root_product_model.code) AS ancestor_codes,
            product.created AS created_date,
            GREATEST(product.updated, COALESCE(sub_product_model.updated, 0), COALESCE(root_product_model.updated, 0)) AS updated_date,
            JSON_KEYS(product.raw_values) AS attribute_codes_in_product_raw_values,
            JSON_MERGE_PRESERVE(
                product.raw_values,
                COALESCE(sub_product_model.raw_values, JSON_OBJECT()), 
                COALESCE(root_product_model.raw_values, JSON_OBJECT())
            ) as raw_values,
            attribute.code AS attribute_as_label_code,
            CASE WHEN root_product_model.id IS NOT NULL THEN 2 ELSE 1 END AS product_lvl_in_attribute_set
        FROM 
            pim_catalog_product product 
            LEFT JOIN pim_catalog_product_model sub_product_model ON sub_product_model.id = product.product_model_id
            LEFT JOIN pim_catalog_product_model root_product_model ON root_product_model.id = sub_product_model.parent_id
            LEFT JOIN pim_catalog_family family ON family.id = product.family_id
            LEFT JOIN pim_catalog_family_variant family_variant ON family_variant.id = sub_product_model.family_variant_id
            LEFT JOIN pim_catalog_attribute attribute ON attribute.id = family.label_attribute_id
        WHERE 
            product.identifier IN (:identifiers)
    ),
    product_categories AS (
        SELECT
            product.id AS product_id,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM 
            product
            JOIN pim_catalog_category_product category_product ON category_product.product_id = product.id
            JOIN pim_catalog_category category ON category.id = category_product.category_id
        GROUP BY product.id
    ),
    ancestor_categories AS (
        SELECT product_id, JSON_ARRAYAGG(category_code) as category_codes
        FROM (
            SELECT product.id AS product_id, category.code AS category_code
            FROM 
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id = model.id
                INNER JOIN pim_catalog_category category ON category.id= category_model.category_id
            UNION ALL
            SELECT product.id AS product_id, category.code AS category_code
            FROM 
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id= parent.id
                INNER JOIN pim_catalog_category category ON category.id = category_model.category_id
        ) results
        GROUP BY product_id
    ),    
    product_groups AS (
        SELECT
            product.id AS product_id,
            JSON_ARRAYAGG(pim_group.code) AS group_codes
        FROM 
            product
            JOIN pim_catalog_group_product group_product ON group_product.product_id = product.id
            JOIN pim_catalog_group pim_group ON pim_group.id = group_product.group_id
        GROUP BY  product.id
    ),
    product_completeness AS (
        SELECT 
            completeness.product_id,
            JSON_OBJECTAGG(channel_code, completeness.completeness_per_locale) as completeness_per_channel
        FROM (
            SELECT
                product_id,
                JSON_OBJECTAGG(
                    locale.code,
                    IF(
                        completeness.required_count = 0, 
                        100, 
                        floor(((completeness.required_count - completeness.missing_count)/completeness.required_count) * 100)
                    )
                ) as completeness_per_locale,
                channel.code as channel_code
            FROM
                product
                JOIN pim_catalog_completeness completeness ON completeness.product_id = product.id
                JOIN pim_catalog_channel channel ON channel.id = completeness.channel_id
                JOIN pim_catalog_locale locale ON locale.id = completeness.locale_id
            GROUP BY product_id, channel_code 
        ) as completeness
        GROUP BY completeness.product_id
    ),
    product_family_label AS (
        SELECT 
            family.family_id,
            JSON_OBJECTAGG(locale.code, family_translation.label) AS labels
        FROM 
            (SELECT DISTINCT product.family_id FROM product WHERE family_id IS NOT NULL) family  
            CROSS JOIN pim_catalog_locale locale
            LEFT JOIN pim_catalog_family_translation family_translation ON family_translation.foreign_key = family.family_id AND family_translation.locale = locale.code
        WHERE locale.is_activated = true
        GROUP BY family.family_id
    ),
    family_attributes AS (
        SELECT 
            family.family_id,
            JSON_ARRAYAGG(attribute.code) as attribute_codes_in_family
        FROM 
        (SELECT DISTINCT product.family_id FROM product WHERE family_id IS NOT NULL) family 
        JOIN pim_catalog_family_attribute family_attribute ON family_attribute.family_id = family.family_id
        JOIN pim_catalog_attribute attribute ON attribute.id = family_attribute.attribute_id
        GROUP BY family.family_id
    ),
    variant_product_attributes AS (
        SELECT 
            family_variant.family_variant_id,
            JSON_ARRAYAGG(attribute.code) as attribute_codes_at_variant_product_level
        FROM 
            (SELECT DISTINCT family_variant_id, product_lvl_in_attribute_set FROM product WHERE family_variant_id IS NOT NULL) family_variant
            JOIN pim_catalog_family_variant_has_variant_attribute_sets family_variant_attribute_set ON family_variant_attribute_set.family_variant_id = family_variant.family_variant_id
            JOIN pim_catalog_variant_attribute_set_has_attributes attribute_in_set ON attribute_in_set.variant_attribute_set_id = family_variant_attribute_set.variant_attribute_sets_id
            JOIN pim_catalog_family_variant_attribute_set attribute_set ON attribute_set.id = family_variant_attribute_set.variant_attribute_sets_id
            JOIN pim_catalog_attribute attribute ON attribute.id = attribute_in_set.attributes_id
        WHERE
            family_variant.product_lvl_in_attribute_set = attribute_set.level
        GROUP BY family_variant.family_variant_id
    )
    SELECT 
        product.id, 
        product.identifier,
        product.is_enabled,
        product.parent_product_model_code,
        product.family_code,
        product.family_variant_code,
        product.ancestor_ids,
        product.ancestor_codes,
        product.created_date,
        product.updated_date,
        product.attribute_codes_in_product_raw_values,
        product.raw_values,
        product.attribute_as_label_code,
        JSON_MERGE_PRESERVE(
            COALESCE(product_categories.category_codes, JSON_ARRAY()),
            COALESCE(ancestor_categories.category_codes, JSON_ARRAY())
        ) AS category_codes,
        COALESCE(ancestor_categories.category_codes, JSON_ARRAY()) as category_codes_of_ancestors,
        COALESCE(product_groups.group_codes, JSON_ARRAY()) AS group_codes,
        COALESCE(product_family_label.labels, JSON_ARRAY()) AS family_labels,
        COALESCE(product_completeness.completeness_per_channel, JSON_OBJECT()) AS completeness,
        COALESCE(family_attributes.attribute_codes_in_family, JSON_ARRAY()) AS attribute_codes_in_family,
        COALESCE(variant_product_attributes.attribute_codes_at_variant_product_level, JSON_ARRAY()) AS attribute_codes_at_variant_product_level
    FROM 
        product
        LEFT JOIN product_groups ON product_groups.product_id = product.id
        LEFT JOIN product_categories ON product_categories.product_id = product.id
        LEFT JOIN ancestor_categories ON ancestor_categories.product_id = product.id
        LEFT JOIN product_family_label ON product_family_label.family_id = product.family_id
        LEFT JOIN product_completeness ON product_completeness.product_id = product.id
        LEFT JOIN family_attributes ON family_attributes.family_id = product.family_id
        LEFT JOIN variant_product_attributes ON variant_product_attributes.family_variant_id = product.family_variant_id
SQL;

        return $this
            ->connection
            ->fetchAll($sql, ['identifiers' => $productIdentifiers], ['identifiers' => Connection::PARAM_STR_ARRAY]);
    }

    private function calculateAttributeCodeAncestors(array $rows): array
    {
        return array_map(function (array $row) {
            if (null === $row['family_variant_code']) {
                $row['attribute_codes_of_ancestor'] = [];

                return $row;
            }

            $attributeCodesInFamily = \json_decode($row['attribute_codes_in_family'], true);
            $attributeCodesAtVariantProductLevel= \json_decode($row['attribute_codes_at_variant_product_level'], true);

            $row['attribute_codes_of_ancestor'] = array_values(array_unique(array_diff($attributeCodesInFamily, $attributeCodesAtVariantProductLevel)));

            return $row;
        }, $rows);
    }

    /**
     * When the product is without any family, the "own level" attribute codes include:
     * - all the attribute codes in the raw values (include optional values)
     *
     * When the product is a variant product, the "own level" attribute codes include:
     * - all the attribute codes defined in attribute set for product level
     * - merged with attribute in raw values for optional attributes
     *
     * When the product is a not a variant product, the "own level" attribute codes include:
     * - all the attribute codes in the family
     * - merged with attribute in raw values for optional attributes
     */
    private function calculateAttributeCodeForOwnLevel(array $rows): array
    {
        return array_map(function (array $row) {
            $attributesInProduct = \json_decode($row['attribute_codes_in_product_raw_values'], true);
            $attributeCodesAtVariantProductLevel = \json_decode($row['attribute_codes_at_variant_product_level'], true);
            $attributeCodesInFamily = \json_decode($row['attribute_codes_in_family'], true);

            if (null === $row['family_code']) {
                $row['attribute_codes_for_this_level'] = $attributesInProduct;
            } elseif (null !== $row['family_variant_code']) {
                $row['attribute_codes_for_this_level'] = array_values(array_unique(array_merge($attributeCodesAtVariantProductLevel, $attributesInProduct)));
            } else {
                $row['attribute_codes_for_this_level'] = array_values(array_unique(array_merge($attributesInProduct, $attributeCodesInFamily)));
            }

            return $row;
        }, $rows);
    }

    /**
     * Create value collection for several products in batch to minimize IO and improve performance.
     *
     * @param [
     *          [
     *              'identifier' => 'foo',
     *              'raw_values' => ['attribute' => ['channel' => ['locale' => 'data' ]]]
     *          ]
     *        ]
     *
     * @return [
     *          'foo' => [
     *              'identifier' => 'foo',
     *              'raw_values' => ['attribute' => ['channel' => ['locale' => 'data' ]]]
     *              'values' => ValueCollection(...)
     *          ]
     *        ]
     */
    private function createValueCollectionInBatchFromRows(array $rows): array
    {
        $rowsIndexedByProductIdentifier = [];
        foreach ($rows as $row) {
            $row['raw_values'] = \json_decode($row['raw_values'], true);
            $rowsIndexedByProductIdentifier[$row['identifier']] = $row;
        }

        $rawValuesCollection = [];
        foreach ($rowsIndexedByProductIdentifier as $identifier => $rowIndexedByProductIdentifier) {
            $rawValuesCollection[$identifier] = $rowIndexedByProductIdentifier['raw_values'];
        }

        $valueCollections = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValuesCollection);
        foreach ($valueCollections as $identifier => $valueCollection) {
            $rowsIndexedByProductIdentifier[$identifier]['values'] = $valueCollection;
        }

        return $rowsIndexedByProductIdentifier;
    }
}
