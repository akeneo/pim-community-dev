<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ElasticsearchProjection;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetElasticsearchProductModelProjection implements GetElasticsearchProductModelProjectionInterface
{
    /** @var Connection */
    private $connection;

    /** @var ReadValueCollectionFactory */
    private $readValueCollectionFactory;

    /** @var NormalizerInterface */
    private $valueCollectionNormalizer;

    /** @var GetAdditionalPropertiesForProductModelProjectionInterface[] */
    private $additionalDataProviders = [];

    public function __construct(
        Connection $connection,
        ReadValueCollectionFactory $readValueCollectionFactory,
        NormalizerInterface $valueCollectionNormalizer,
        iterable $additionalDataProviders
    ) {
        $this->connection = $connection;
        $this->readValueCollectionFactory = $readValueCollectionFactory;
        $this->valueCollectionNormalizer = $valueCollectionNormalizer;
        $this->additionalDataProviders = $additionalDataProviders;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        $valuesAndProperties = $this->getValuesAndPropertiesFromProductModelCodes($productModelCodes);
        $completeFilters = $this->getCompleteFilterFromProductModelCodes($productModelCodes);
        $attributes = $this->getAttributesFromProductModelCodes($productModelCodes);

        $productModelProjections = [];

        $rowCodes = array_map(function (array $row) {
            return $row['code'];
        }, $valuesAndProperties);

        $diffCodes = array_diff($productModelCodes, $rowCodes);
        if (count($diffCodes) > 0) {
            throw new ObjectNotFoundException(
                sprintf('Product model codes "%s" were not found.', implode(',', $diffCodes))
            );
        }

        foreach ($productModelCodes as $productModelCode) {
            $productModelProjections[$productModelCode] = new ElasticsearchProductModelProjection(
                $valuesAndProperties[$productModelCode]['id'],
                $valuesAndProperties[$productModelCode]['code'],
                $valuesAndProperties[$productModelCode]['created'],
                $valuesAndProperties[$productModelCode]['updated'],
                $valuesAndProperties[$productModelCode]['family_code'],
                $valuesAndProperties[$productModelCode]['family_labels'],
                $valuesAndProperties[$productModelCode]['family_variant_code'],
                $valuesAndProperties[$productModelCode]['category_codes'],
                $valuesAndProperties[$productModelCode]['ancestor_category_codes'],
                $valuesAndProperties[$productModelCode]['parent_code'],
                $valuesAndProperties[$productModelCode]['values'],
                $completeFilters[$productModelCode]['all_complete'],
                $completeFilters[$productModelCode]['all_incomplete'],
                $valuesAndProperties[$productModelCode]['parent_id'],
                $valuesAndProperties[$productModelCode]['labels'],
                $attributes[$productModelCode]['ancestor_attribute_codes'],
                $attributes[$productModelCode]['attributes_for_this_level']
            );
        }

        foreach ($this->additionalDataProviders as $additionalDataProvider) {
            $additionalDataPerProductModel = $additionalDataProvider->fromProductModelCodes($productModelCodes);
            foreach ($additionalDataPerProductModel as $productModelCode => $additionalData) {
                $productModelProjections[$productModelCode] = $productModelProjections[$productModelCode]->addAdditionalData($additionalData);
            }
        }

        return $productModelProjections;
    }

    private function getValuesAndPropertiesFromProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH
    product_model AS (
        SELECT
            product_model.id,
            product_model.code,
            product_model.created,
            root_product_model.code AS parent_code,
            GREATEST(product_model.updated, COALESCE(root_product_model.updated, 0)) as updated,
            JSON_MERGE_PRESERVE(COALESCE(root_product_model.raw_values, '{}'), COALESCE(product_model.raw_values, '{}')) AS raw_values,
            family.code AS family_code,
            family_variant.code AS family_variant_code,
            product_model.parent_id,
            family_variant.family_id,
            family_variant.id AS family_variant_id,
            attribute.code AS attribute_as_label_code
        FROM
            pim_catalog_product_model product_model
            INNER JOIN pim_catalog_family_variant family_variant ON family_variant.id = product_model.family_variant_id
            INNER JOIN pim_catalog_family family ON family.id = family_variant.family_id
            INNER JOIN pim_catalog_attribute attribute ON family.label_attribute_id = attribute.id 
            LEFT JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
        WHERE
            product_model.code IN (:productModelCodes)
    ),
    product_model_categories AS (
        SELECT
            product_model.id as product_model_id,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product_model
            INNER JOIN pim_catalog_category_product_model category_product_model ON category_product_model.product_model_id = product_model.id
            INNER JOIN pim_catalog_category category ON category.id = category_product_model.category_id
        GROUP BY product_model.id
    ),
    root_product_model_categories AS (
        SELECT
            product_model.id AS product_model_id,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product_model
            INNER JOIN pim_catalog_category_product_model category_product_model ON category_product_model.product_model_id = product_model.parent_id
            INNER JOIN pim_catalog_category category ON category.id = category_product_model.category_id
        GROUP BY product_model.id
    ),
    product_model_family_labels AS (
        SELECT 
            family.family_id,
            JSON_OBJECTAGG(locale.code, family_translation.label) AS labels
        FROM 
            (SELECT DISTINCT product_model.family_id FROM product_model) family  
            CROSS JOIN pim_catalog_locale locale
            LEFT JOIN pim_catalog_family_translation family_translation ON family_translation.foreign_key = family.family_id AND family_translation.locale = locale.code
        WHERE locale.is_activated = true
        GROUP BY family.family_id
    )
    SELECT
        product_model.*,
        JSON_MERGE_PRESERVE(
            COALESCE(product_model_categories.category_codes, JSON_ARRAY()),
            COALESCE(root_product_model_categories.category_codes, JSON_ARRAY())
        ) AS category_codes,
        COALESCE(root_product_model_categories.category_codes, JSON_ARRAY()) as ancestor_category_codes,
        COALESCE(product_model_family_labels.labels, JSON_ARRAY()) AS family_labels
    FROM product_model
    LEFT JOIN product_model_categories ON product_model_categories.product_model_id = product_model.id
    LEFT JOIN root_product_model_categories ON root_product_model_categories.product_model_id = product_model.id
    LEFT JOIN product_model_family_labels ON product_model_family_labels.family_id = product_model.family_id
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $rows = $this->createValueCollectionInBatchFromRows($rows);

        $platform = $this->connection->getDatabasePlatform();
        $results = [];
        foreach ($rows as $row) {
            $values = $row['raw_values'];

            $results[$row['code']] = [
                'id' => (int) $row['id'],
                'code' => $row['code'],
                'created' => \DateTimeImmutable::createFromMutable(
                    Type::getType(Type::DATETIME)->convertToPhpValue($row['created'], $platform)
                ),
                'updated' => \DateTimeImmutable::createFromMutable(
                    Type::getType(Type::DATETIME)->convertToPhpValue($row['updated'], $platform)
                ),
                'family_code' => $row['family_code'],
                'family_labels' => json_decode($row['family_labels'], true),
                'family_variant_code' => $row['family_variant_code'],
                'category_codes' => json_decode($row['category_codes'], true),
                'ancestor_category_codes' => json_decode($row['ancestor_category_codes'], true),
                'parent_code' => $row['parent_code'],
                'values' => $this->valueCollectionNormalizer->normalize($row['values'], ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX),
                'parent_id' => $row['parent_id'] ? (int) $row['parent_id'] : null,
                'labels' => isset($values[$row['attribute_as_label_code']]) ? $values[$row['attribute_as_label_code']] : [],
            ];
        }

        return $results;
    }

    /**
     * The 'all_complete' field means every product is complete, i.e. has a missing_count at 0. In other words,
     * the sum of the missing attributes is 0.
     * The 'all_incomplete' field means every product is incomplete, i.e. there is no product with a missing_count
     * at 0. In other words, the minimal value of the missing attributes should not be 0.
     */
    private function getCompleteFilterFromProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH product_model_completeness_by_channel_id_and_locale_id AS (
    SELECT
        product_model.code AS product_model_code,
        completeness.locale_id,
        completeness.channel_id,
        SUM(completeness.missing_count) = 0 AS all_complete,
        MIN(completeness.missing_count) <> 0 AS all_incomplete
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product product ON product.product_model_id = product_model.id
    INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
    WHERE product_model.code IN (:productModelCodes)
    GROUP BY product_model_code, completeness.locale_id, completeness.channel_id
UNION ALL
    SELECT
        root_product_model.code AS product_model_code,
        completeness.locale_id,
        completeness.channel_id,
        SUM(completeness.missing_count) = 0 AS allcomplete,
        MIN(completeness.missing_count) <> 0 AS allincomplete
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
    INNER JOIN pim_catalog_product product ON product.product_model_id = product_model.id
    INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
    WHERE root_product_model.code IN (:productModelCodes)
    GROUP BY product_model_code, completeness.locale_id, completeness.channel_id
), 
product_model_completeness_by_channel AS (
    SELECT
         product_model_code,
         channel.code AS channel_code,
         JSON_OBJECTAGG(locale.code, all_complete) AS all_complete,
         JSON_OBJECTAGG(locale.code, all_incomplete) AS all_incomplete
    FROM product_model_completeness_by_channel_id_and_locale_id product_model_completeness
    JOIN pim_catalog_channel channel ON channel.id = product_model_completeness.channel_id
    JOIN pim_catalog_locale locale ON locale.id = product_model_completeness.locale_id
    GROUP BY product_model_code, channel_code
)
SELECT
    product_model_code,
    JSON_OBJECTAGG(channel_code, all_complete) AS all_complete,
    JSON_OBJECTAGG(channel_code, all_incomplete) AS all_incomplete
FROM product_model_completeness_by_channel
GROUP BY product_model_code
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = array_fill_keys(
            $productModelCodes,
            [
                'all_complete' => [],
                'all_incomplete' => [],
            ]
        );

        foreach ($rows as $row) {
            $results[$row['product_model_code']] = [
                'all_complete' => json_decode($row['all_complete'], true),
                'all_incomplete' => json_decode($row['all_incomplete'], true),
            ];
        }

        return $results;
    }

    /**
     * Ancestor attribute codes:
     * - root product model = no parent = no ancestor attribute codes
     * - sub product model = with parent = common attributes as ancestor attribute codes
     *      common attribute = family attributes MINUS all attribute codes in attribute set level 1 (product model) and attribute set level 2 (product)
     *
     * Attributes for this level =
     * - root product model with sub product model = common attributes
     *      common attribute = family attributes MINUS all attribute codes in attribute set level 1 (product model) and attribute set level 2 (product)
     * - root product model without sub product model = common attributes
     *      common attribute = family attributes MINUS all attribute codes in attribute set level 1 (product)
     * - sub product model (with a parent) = all attribute codes in attribute set level 1 (as level 2 is for the product)
     */
    private function getAttributesFromProductModelCodes(array $productModelCodes): array
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
family_variant_attributes_per_level AS (
    SELECT 
        family_variant_id, 
        JSON_OBJECTAGG(level, attribute_codes) as attribute_codes_per_level
    FROM (
        SELECT
            product_family_variant.id AS family_variant_id,
            attribute_set.level,
            JSON_ARRAYAGG(attribute.code) AS attribute_codes
        FROM (
            SELECT DISTINCT(product_model.family_variant_id) AS id
            FROM pim_catalog_product_model product_model
            WHERE product_model.code IN (:productModelCodes)
        ) AS product_family_variant
        INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets variant_set ON product_family_variant.id = variant_set.family_variant_id
        INNER JOIN pim_catalog_family_variant_attribute_set attribute_set ON attribute_set.id = variant_set.variant_attribute_sets_id
        INNER JOIN pim_catalog_variant_attribute_set_has_attributes variant_attributes ON variant_attributes.variant_attribute_set_id = variant_set.variant_attribute_sets_id
        INNER JOIN pim_catalog_attribute attribute ON attribute.id = variant_attributes.attributes_id
        GROUP BY family_variant_id, attribute_set.level
    ) as family_variant_attributes_per_level
    GROUP BY family_variant_id
)
SELECT
    product_model.code AS code,
    product_model.parent_id AS parent_id,
    family_attributes.attribute_codes AS family_attribute_codes,
    family_variant_attributes_per_level.attribute_codes_per_level
FROM pim_catalog_product_model product_model
INNER JOIN pim_catalog_family_variant family_variant ON family_variant.id = product_model.family_variant_id
INNER JOIN family_attributes ON family_attributes.family_id = family_variant.family_id
INNER JOIN family_variant_attributes_per_level ON family_variant_attributes_per_level.family_variant_id = product_model.family_variant_id
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
            $familyAttributes = json_decode($row['family_attribute_codes'], true);
            $familyVariantAttributeCodesPerLevel = json_decode($row['attribute_codes_per_level'], true);
            $variantAttributeCodes = array_merge(...array_values($familyVariantAttributeCodesPerLevel));
            $commonAttributeCodes = array_values(array_diff($familyAttributes, $variantAttributeCodes));

            $results[$row['code']] = [
                'ancestor_attribute_codes' => null === $row['parent_id'] ? [] : $commonAttributeCodes,
                'attributes_for_this_level' => null === $row['parent_id'] ? $commonAttributeCodes : $familyVariantAttributeCodesPerLevel['1'],
            ];
        }

        return $results;
    }

    /**
     * Create value collection for several product models in batch to minimize IO and improve performance.
     *
     * @param [
     *          [
     *              'code' => 'foo',
     *              'raw_values' => ['attribute' => ['channel' => ['locale' => 'data' ]]]
     *          ]
     *        ]
     *
     * @return [
     *          'foo' => [
     *              'code' => 'foo',
     *              'raw_values' => ['attribute' => ['channel' => ['locale' => 'data' ]]]
     *              'values' => ValueCollection(...)
     *          ]
     *        ]
     */
    private function createValueCollectionInBatchFromRows(array $rows): array
    {
        $rowsIndexedByProductModelCode = [];
        foreach ($rows as $row) {
            $row['raw_values'] = \json_decode($row['raw_values'], true);
            $rowsIndexedByProductModelCode[$row['code']] = $row;
        }

        $rawValuesCollection = [];
        foreach ($rowsIndexedByProductModelCode as $code => $rowIndexedByProductModelCode) {
            $rawValuesCollection[$code] = $rowIndexedByProductModelCode['raw_values'];
        }

        $valueCollections = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValuesCollection);
        foreach ($valueCollections as $code => $valueCollection) {
            $rowsIndexedByProductModelCode[$code]['values'] = $valueCollection;
        }

        return $rowsIndexedByProductModelCode;
    }
}
