<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ElasticsearchProjection;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetElasticsearchProductProjection implements GetElasticsearchProductProjectionInterface
{
    private const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_product_model';

    /**
     * @param GetAdditionalPropertiesForProductProjectionInterface[] $additionalDataProviders
     */
    public function __construct(
        private Connection $connection,
        private NormalizerInterface $valuesNormalizer,
        private ReadValueCollectionFactory $readValueCollectionFactory,
        private LoggerInterface $logger,
        private iterable $additionalDataProviders = []
    ) {
        Assert::allIsInstanceOf(
            $this->additionalDataProviders,
            GetAdditionalPropertiesForProductProjectionInterface::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $productUuids): iterable
    {
        if (empty($productUuids)) {
            return [];
        }

        $rows = $this->fetchRows($productUuids);

        // TODO remove CPM-1042
        if ($this->newCompletenessTableExists()) {
            $rows = $this->getCompletenesses($rows);
        }

        $rows = $this->calculateAttributeCodeAncestors($rows);
        $rows = $this->calculateAttributeCodeForOwnLevel($rows);

        $rowUuids = \array_map(
            static fn (array $row): string => (string) $row['uuid'],
            $rows
        );
        $notFetchedUuids = \array_diff(
            array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $productUuids),
            $rowUuids
        );

        if (\count($notFetchedUuids) > 0) {
            $this->logger->warning(\sprintf('Trying to get ES product projection from product uuids "%s" which does not exist', \implode(',', $notFetchedUuids)));
        }

        $rows = $this->createValueCollectionInBatchFromRows($rows);

        $context = ['value_collections' => \array_map(
            static fn (array $row) => $row['values'],
            $rows
        )];
        $additionalData = [];
        /** @var GetAdditionalPropertiesForProductProjectionInterface $additionalDataProvider */
        foreach ($this->additionalDataProviders as $additionalDataProvider) {
            $additionalData = \array_replace_recursive(
                $additionalData,
                $additionalDataProvider->fromProductUuids($productUuids, $context)
            );
        }

        $platform = $this->connection->getDatabasePlatform();
        foreach ($rows as $row) {
            $productUuid = (string) $row['uuid'];
            $rawValues = $row['raw_values'];

            $productLabels = [];
            if (null !== $row['attribute_as_label_code'] && isset($rawValues[$row['attribute_as_label_code']])) {
                $productLabels = $rawValues[$row['attribute_as_label_code']];
            }

            $values = $this->valuesNormalizer->normalize($row['values'], self::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX);

            $projection = new ElasticsearchProductProjection(
                Uuid::fromString($row['uuid']),
                $row['identifier'],
                Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['created_date'], $platform),
                Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['updated_date'], $platform),
                Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($row['entity_updated_date'], $platform),
                (bool) $row['is_enabled'],
                $row['family_code'],
                \json_decode($row['family_labels'], true),
                $row['family_variant_code'],
                \json_decode($row['category_codes'], true),
                \json_decode($row['category_codes_of_ancestors'], true),
                \json_decode($row['group_codes'], true),
                \json_decode($row['completeness'] ?? '{}', true),
                $row['parent_product_model_code'],
                $values,
                array_filter(\json_decode($row['ancestor_ids'], true)),
                array_filter(\json_decode($row['ancestor_codes'], true)),
                $productLabels,
                $row['attribute_codes_of_ancestor'],
                $row['attribute_codes_for_this_level']
            );

            yield $productUuid => $projection->addAdditionalData($additionalData[$productUuid] ?? []);
        }
    }

    private function fetchRows(array $productUuids): array
    {
        if ($this->newCompletenessTableExists()) {
            $sql = <<<SQL
WITH 
    main_identifier AS (
        SELECT id
        FROM pim_catalog_attribute
        WHERE main_identifier = 1
        LIMIT 1
    ),
    product as (
        SELECT
            product.uuid,
            pim_catalog_product_unique_data.raw_data AS identifier,
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
            product.updated AS entity_updated_date,
            COALESCE(JSON_KEYS(product.raw_values), JSON_OBJECT()) AS attribute_codes_in_product_raw_values,
            JSON_MERGE_PATCH(
                product.raw_values,
                COALESCE(sub_product_model.raw_values, JSON_OBJECT()),
                COALESCE(root_product_model.raw_values, JSON_OBJECT())
            ) as raw_values,
            attribute.code AS attribute_as_label_code,
            CASE WHEN root_product_model.id IS NOT NULL THEN 2 ELSE 1 END AS product_lvl_in_attribute_set
        FROM
            pim_catalog_product product
            LEFT JOIN pim_catalog_product_unique_data 
                ON pim_catalog_product_unique_data.product_uuid = product.uuid
                AND pim_catalog_product_unique_data.attribute_id = (SELECT id FROM main_identifier)
            LEFT JOIN pim_catalog_product_model sub_product_model ON sub_product_model.id = product.product_model_id
            LEFT JOIN pim_catalog_product_model root_product_model ON root_product_model.id = sub_product_model.parent_id
            LEFT JOIN pim_catalog_family family ON family.id = product.family_id
            LEFT JOIN pim_catalog_family_variant family_variant ON family_variant.id = sub_product_model.family_variant_id
            LEFT JOIN pim_catalog_attribute attribute ON attribute.id = family.label_attribute_id
        WHERE
            product.uuid IN (:uuids)
    ),
    product_categories AS (
        SELECT
            product.uuid AS product_uuid,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product
            JOIN pim_catalog_category_product category_product ON category_product.product_uuid = product.uuid
            JOIN pim_catalog_category category ON category.id = category_product.category_id
        GROUP BY product.uuid
    ),
    ancestor_categories AS (
        SELECT product_uuid, JSON_ARRAYAGG(category_code) as category_codes
        FROM (
            SELECT product.uuid AS product_uuid, category.code AS category_code
            FROM
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id = model.id
                INNER JOIN pim_catalog_category category ON category.id= category_model.category_id
            UNION ALL
            SELECT product.uuid AS product_uuid, category.code AS category_code
            FROM
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id= parent.id
                INNER JOIN pim_catalog_category category ON category.id = category_model.category_id
        ) results
        GROUP BY product_uuid
    ),
    product_groups AS (
        SELECT
            product.uuid AS product_uuid,
            JSON_ARRAYAGG(pim_group.code) AS group_codes
        FROM
            product
            JOIN pim_catalog_group_product group_product ON group_product.product_uuid = product.uuid
            JOIN pim_catalog_group pim_group ON pim_group.id = group_product.group_id
        GROUP BY product.uuid
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
        BIN_TO_UUID(product.uuid) as uuid,
        product.identifier,
        product.is_enabled,
        product.parent_product_model_code,
        product.family_code,
        product.family_variant_code,
        product.ancestor_ids,
        product.ancestor_codes,
        product.created_date,
        product.updated_date,
        product.entity_updated_date,
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
        COALESCE(family_attributes.attribute_codes_in_family, JSON_ARRAY()) AS attribute_codes_in_family,
        COALESCE(variant_product_attributes.attribute_codes_at_variant_product_level, JSON_ARRAY()) AS attribute_codes_at_variant_product_level
    FROM
        product
        LEFT JOIN product_groups ON product_groups.product_uuid = product.uuid
        LEFT JOIN product_categories ON product_categories.product_uuid = product.uuid
        LEFT JOIN ancestor_categories ON ancestor_categories.product_uuid = product.uuid
        LEFT JOIN product_family_label ON product_family_label.family_id = product.family_id
        LEFT JOIN family_attributes ON family_attributes.family_id = product.family_id
        LEFT JOIN variant_product_attributes ON variant_product_attributes.family_variant_id = product.family_variant_id
SQL;
        // TODO remove CPM-1042
        } else {
            $sql = <<<SQL
WITH 
    main_identifier AS (
        SELECT id
        FROM pim_catalog_attribute
        WHERE main_identifier = 1
        LIMIT 1
    ),
    product as (
        SELECT
            product.uuid,
            pim_catalog_product_unique_data.raw_data AS identifier,
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
            product.updated AS entity_updated_date,
            COALESCE(JSON_KEYS(product.raw_values), JSON_OBJECT()) AS attribute_codes_in_product_raw_values,
            JSON_MERGE_PATCH(
                product.raw_values,
                COALESCE(sub_product_model.raw_values, JSON_OBJECT()),
                COALESCE(root_product_model.raw_values, JSON_OBJECT())
            ) as raw_values,
            attribute.code AS attribute_as_label_code,
            CASE WHEN root_product_model.id IS NOT NULL THEN 2 ELSE 1 END AS product_lvl_in_attribute_set
        FROM
            pim_catalog_product product
            LEFT JOIN pim_catalog_product_unique_data
                ON pim_catalog_product_unique_data.product_uuid = product.uuid
                AND pimè_catalog_product_unique_data.attribute_id = (SELECT id FROM main_identifier)
            LEFT JOIN pim_catalog_product_model sub_product_model ON sub_product_model.id = product.product_model_id
            LEFT JOIN pim_catalog_product_model root_product_model ON root_product_model.id = sub_product_model.parent_id
            LEFT JOIN pim_catalog_family family ON family.id = product.family_id
            LEFT JOIN pim_catalog_family_variant family_variant ON family_variant.id = sub_product_model.family_variant_id
            LEFT JOIN pim_catalog_attribute attribute ON attribute.id = family.label_attribute_id
        WHERE
            product.uuid IN (:uuids)
    ),
    product_categories AS (
        SELECT
            product.uuid AS product_uuid,
            JSON_ARRAYAGG(category.code) AS category_codes
        FROM
            product
            JOIN pim_catalog_category_product category_product ON category_product.product_uuid = product.uuid
            JOIN pim_catalog_category category ON category.id = category_product.category_id
        GROUP BY product.uuid
    ),
    ancestor_categories AS (
        SELECT product_uuid, JSON_ARRAYAGG(category_code) as category_codes
        FROM (
            SELECT product.uuid AS product_uuid, category.code AS category_code
            FROM
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id = model.id
                INNER JOIN pim_catalog_category category ON category.id= category_model.category_id
            UNION ALL
            SELECT product.uuid AS product_uuid, category.code AS category_code
            FROM
                product
                INNER JOIN pim_catalog_product_model model ON model.id = product.parent_product_model_id
                INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
                INNER JOIN pim_catalog_category_product_model category_model ON category_model.product_model_id= parent.id
                INNER JOIN pim_catalog_category category ON category.id = category_model.category_id
        ) results
        GROUP BY product_uuid
    ),
    product_groups AS (
        SELECT
            product.uuid AS product_uuid,
            JSON_ARRAYAGG(pim_group.code) AS group_codes
        FROM
            product
            JOIN pim_catalog_group_product group_product ON group_product.product_uuid = product.uuid
            JOIN pim_catalog_group pim_group ON pim_group.id = group_product.group_id
        GROUP BY product.uuid
    ),
    product_completeness AS (
        SELECT
            completeness.product_uuid,
            JSON_OBJECTAGG(channel_code, completeness.completeness_per_locale) as completeness_per_channel
        FROM (
            SELECT
                product_uuid,
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
                STRAIGHT_JOIN pim_catalog_completeness completeness ON completeness.product_uuid = product.uuid
                JOIN pim_catalog_channel channel ON channel.id = completeness.channel_id
                JOIN pim_catalog_locale locale ON locale.id = completeness.locale_id
            GROUP BY product_uuid, channel_code
        ) as completeness
        GROUP BY completeness.product_uuid
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
        BIN_TO_UUID(product.uuid) as uuid,
        product.identifier,
        product.is_enabled,
        product.parent_product_model_code,
        product.family_code,
        product.family_variant_code,
        product.ancestor_ids,
        product.ancestor_codes,
        product.created_date,
        product.updated_date,
        product.entity_updated_date,
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
        LEFT JOIN product_groups ON product_groups.product_uuid = product.uuid
        LEFT JOIN product_categories ON product_categories.product_uuid = product.uuid
        LEFT JOIN ancestor_categories ON ancestor_categories.product_uuid = product.uuid
        LEFT JOIN product_family_label ON product_family_label.family_id = product.family_id
        LEFT JOIN product_completeness ON product_completeness.product_uuid = product.uuid
        LEFT JOIN family_attributes ON family_attributes.family_id = product.family_id
        LEFT JOIN variant_product_attributes ON variant_product_attributes.family_variant_id = product.family_variant_id
SQL;
        }
        $productUuidsAsBytes = \array_map(static fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $rows =  $this
            ->connection
            ->fetchAllAssociative($sql, ['uuids' => $productUuidsAsBytes], ['uuids' => Connection::PARAM_STR_ARRAY]);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['uuid']] = $row;
        }

        return $result;
    }

    private function calculateAttributeCodeAncestors(array $rows): array
    {
        return \array_map(static function (array $row) {
            if (null === $row['family_variant_code']) {
                $row['attribute_codes_of_ancestor'] = [];

                return $row;
            }

            $attributeCodesInFamily = \json_decode($row['attribute_codes_in_family'], true);
            $attributeCodesAtVariantProductLevel = \json_decode($row['attribute_codes_at_variant_product_level'], true);

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
        return \array_map(static function (array $row) {
            $attributesInProduct = \json_decode($row['attribute_codes_in_product_raw_values'], true);
            $attributeCodesAtVariantProductLevel = \json_decode($row['attribute_codes_at_variant_product_level'], true);
            $attributeCodesInFamily = \json_decode($row['attribute_codes_in_family'], true);

            if (null === $row['family_code']) {
                $row['attribute_codes_for_this_level'] = $attributesInProduct;
            } elseif (null !== $row['family_variant_code']) {
                $row['attribute_codes_for_this_level'] = \array_values(\array_unique([...$attributeCodesAtVariantProductLevel, ...$attributesInProduct]));
            } else {
                $row['attribute_codes_for_this_level'] = \array_values(\array_unique([...$attributesInProduct, ...$attributeCodesInFamily]));
            }

            return $row;
        }, $rows);
    }

    /**
     * Create value collection for several products in batch to minimize IO and improve performance.
     *
     * @param array $rows [
     *          [
     *              'identifier' => 'foo',
     *              'raw_values' => '{"attribute":{"channel":{"locale":"data"}}},
     *              ... => ...,
     *          ]
     *        ]
     *
     * @return array [
     *          'foo' => [
     *              'identifier' => 'foo',
     *              'raw_values' => ['attribute' => ['channel' => ['locale' => 'data' ]]],
     *              'values' => ValueCollection(...),
     *              ... => ...
     *          ]
     *        ]
     */
    private function createValueCollectionInBatchFromRows(array $rows): array
    {
        $rowsIndexedByProductUuid = [];
        foreach ($rows as $row) {
            $row['raw_values'] = \json_decode($row['raw_values'], true);
            $rowsIndexedByProductUuid[$row['uuid']] = $row;
        }

        $valueCollections = $this->readValueCollectionFactory->createMultipleFromStorageFormat(
            \array_map(
                static fn (array $row): array => $row['raw_values'],
                $rowsIndexedByProductUuid
            )
        );
        foreach ($valueCollections as $uuid => $valueCollection) {
            $rowsIndexedByProductUuid[$uuid]['values'] = $valueCollection;
        }

        return $rowsIndexedByProductUuid;
    }

    private function getCompletenesses(array $rows): array
    {
        $query = <<<SQL
            SELECT bin_to_uuid(product_uuid) as uuid, completeness
            FROM pim_catalog_product_completeness
            WHERE product_uuid in (:uuids)
            SQL;

        $results = $this->connection->fetchAllKeyValue(
            $query,
            [
                'uuids' => \array_map(static fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), \array_keys($rows)),
            ],
            [
                'uuids' => Connection::PARAM_STR_ARRAY
            ]
        );

        foreach ($results as $uuid => $value) {
            $completenesses = \json_decode($value, true);
            $completenessesByUuid = [];
            foreach ($completenesses as $channelCode => $completenessByLocale) {
                foreach ($completenessByLocale as $localeCode => $value) {
                    $ratio = (int) floor(100 * ($value['required'] - $value['missing']) / $value['required']);
                    $completenessesByUuid[$channelCode][$localeCode] = $ratio;
                }
            }
            $rows[$uuid]['completeness'] = json_encode($completenessesByUuid);
        }

        return $rows;
    }

    private function newCompletenessTableExists(): bool
    {
        $TABLE_NAME = 'pim_catalog_product_completeness';

        return $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $TABLE_NAME,
            ]
        )->rowCount() >= 1;
    }
}
