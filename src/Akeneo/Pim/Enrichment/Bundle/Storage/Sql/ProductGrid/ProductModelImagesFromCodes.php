<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Doctrine\DBAL\Connection;

/**
 * When requesting the label of product model in the datagrid, you can get it from:
 * - the parent product model
 *      if the attribute as image is at the level 0 and the product model is not root
 *
 * - the current product model
 *      if the attribute as image is at the level 0 and the current product model is root
 *      OR
 *      if the attribute as image is at the level 1 and the current product model is a sub product model
 *
 * - a sub product model
 *      if the attribute as image is at the level 1 and the current product model is a root product model
 *      it will be the image of the oldest sub product model by creation date with a non null image
 *
 * - a child variant product:
 *      if the attribute as image is at the level 2 and the current product model
 *      it will be the image of the oldest sub product model by creation date with a non null image
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelImagesFromCodes
{
    /** @var Connection */
    private $connection;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    public function __construct(Connection $connection, WriteValueCollectionFactory $valueCollectionFactory)
    {
        $this->connection = $connection;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * @param array  $codes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return array product model images index by product model code
     *     [
     *        'product_model_code' => ['image' => MediaValue]
     *     ]
     */
    public function __invoke(array $codes, string $channelCode, string $localeCode): array
    {
        $codesPerImageLevel = $this->codesPerImageLevel($codes);

        $images = array_replace_recursive(
            $this->getImagesFromCurrentOrParentProductModel(
                $codesPerImageLevel['image_in_current_or_parent_product_model'],
                $channelCode,
                $localeCode
            ),
            $this->getImagesFromSubProductModel(
                $codesPerImageLevel['image_in_sub_product_model'],
                $channelCode,
                $localeCode
            ),
            $this->getImagesFromVariantProduct(
                $codesPerImageLevel['image_in_variant_product'],
                $channelCode,
                $localeCode
            )
        );

        return $images;
    }

    /**
     * Compute at which level is the image attribute.
     * It can be:
     * - in the current product model or in its parent
     * - in the sub product model if the product model is a root product model
     * - in the variant product model
     *
     * @param array $codes
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     *              [
     *                'image_in_current_or_parent_product_model' => ['product_model_1']
     *                'image_in_sub_product_model' => ['product_model_2']
     *                'image_in_variant_product' => ['product_model_3']
     *              ]
     *
     */
    private function codesPerImageLevel(array $codes): array
    {
        $codesPerLevel = [
            'image_in_sub_product_model' => [],
            'image_in_variant_product' => [],
            'image_in_current_or_parent_product_model' => [],
        ];

        $sql = <<<SQL
            SELECT 
                product_model_code,
                CASE
                    WHEN product_model_level = 0 AND image_code_level = 1 AND number_level = 2 THEN 'image_in_sub_product_model'
                    WHEN product_model_level = 0 AND image_code_level = 1 AND number_level = 1 THEN 'image_in_variant_product'
                    WHEN product_model_level = 0 AND image_code_level = 2 THEN 'image_in_variant_product'
                    WHEN product_model_level = 1 AND image_code_level = 1 THEN 'image_in_current_or_parent_product_model'
                    WHEN product_model_level = 1 AND image_code_level = 2 THEN 'image_in_variant_product'
                    ELSE 'image_in_current_or_parent_product_model' END 
                AS image_level
            FROM (
                SELECT 
                    pm.code as product_model_code,
                    COUNT(all_attribute_sets.family_variant_id) as number_level,
                    pm.lvl as product_model_level,
                    fv_set.level as image_code_level
                FROM
                    pim_catalog_product_model pm
                    JOIN pim_catalog_family_variant fv ON fv.id = pm.family_variant_id
                    JOIN pim_catalog_family f ON f.id = fv.family_id
                    JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
                    JOIN pim_catalog_family_variant_has_variant_attribute_sets attr_set ON  attr_set.family_variant_id = fv.id
                    JOIN pim_catalog_family_variant_attribute_set fv_set ON fv_set.id = variant_attribute_sets_id
                    JOIN pim_catalog_variant_attribute_set_has_attributes attr ON attr.variant_attribute_set_id = fv_set.id AND attr.attributes_id = a_image.id
                    JOIN pim_catalog_family_variant_has_variant_attribute_sets all_attribute_sets ON  all_attribute_sets.family_variant_id = fv.id
                WHERE 
                    pm.code IN (:codes)
                GROUP BY pm.code, all_attribute_sets.family_variant_id, fv_set.level
            ) as product_models
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        foreach ($rows as $row) {
            $codesPerLevel[$row['image_level']][] = $row['product_model_code'];
            $key = array_search($row['product_model_code'], $codes);
            unset($codes[$key]);
        }

        // product model codes are not returned by the SQL request if the attribute as image is at level 0
        $codesPerLevel['image_in_current_or_parent_product_model'] = array_merge($codesPerLevel['image_in_current_or_parent_product_model'], $codes);

        return $codesPerLevel;
    }

    private function getImagesFromCurrentOrParentProductModel(array $codes, string $channelCode, string $localeCode): array
    {
        $images = [];
        foreach ($codes as $code) {
            $images[$code]['image'] = null;
        }

        $sql = <<<SQL
            SELECT 
                pm.code,
                a_image.code as attribute_code,
                a_image.is_localizable,
                a_image.is_scopable,
                JSON_MERGE(pm.raw_values, COALESCE(root_pm.raw_values, '{}')) as raw_values 
            FROM
                pim_catalog_product_model pm
                LEFT JOIN pim_catalog_product_model root_pm ON root_pm.id = pm.parent_id
                JOIN pim_catalog_family_variant fv ON fv.id = pm.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE 
                pm.code IN (:codes)
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $productModels = [];
        $productModelsInfo = [];

        foreach ($rows as $row) {
            $rawValues = json_decode($row['raw_values'], true);
            $filteredRawValues = array_intersect_key($rawValues, [$row['attribute_code'] => true]);
            $productModels[$row['code']] = $filteredRawValues;
            $productModelsInfo[$row['code']]['is_scopable'] = $row['is_scopable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['is_localizable'] = $row['is_localizable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['attribute_code'] = $row['attribute_code'];
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($productModels);

        foreach ($valueCollections as $productModelCode => $valueCollection) {
            $productModelInfo = $productModelsInfo[$productModelCode];

            $images[$productModelCode]['image'] = $valueCollection->getByCodes(
                $productModelInfo['attribute_code'],
                $productModelInfo['is_scopable'],
                $productModelInfo['is_localizable']
            );
        }

        return $images;
    }

    /**
     * It gets image from the oldest child product model having a non null image.
     *
     * As we can't get easily the first row of each group when doing a group by,
     * it executes one request per product model.
     *
     * @param array  $codes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     */
    private function getImagesFromSubProductModel(array $codes, string $channelCode, string $localeCode): array
    {
        $images = [];
        foreach ($codes as $code) {
            $images[$code]['image'] = null;
        }

        $sql = <<<SQL
            SELECT 
                pm_root.code,
                a_image.code as attribute_code,
                pm_child.raw_values,
                a_image.is_localizable,
                a_image.is_scopable,
                JSON_EXTRACT(
                    pm_child.raw_values,
                    CONCAT('$."', a_image.code, '".', IF(is_scopable = 1, '":channel_code"', '"<all_channels>"'), '.', IF(is_localizable = 1, '":locale_code"', '"<all_locales>"'))
                ) as image_value
            FROM
                pim_catalog_product_model pm_root
                JOIN pim_catalog_product_model pm_child ON pm_child.parent_id = pm_root.id
                JOIN pim_catalog_family_variant fv ON fv.id = pm_root.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE
                pm_root.code = :code
            HAVING
                image_value IS NOT NULL AND JSON_TYPE(image_value) != 'NULL'
            ORDER BY 
                pm_child.created ASC
            LIMIT 1
SQL;

        $productModels = [];
        $productModelsInfo = [];
        foreach ($codes as $code) {
            $row = $this->connection->executeQuery(
                $sql,
                ['code' => $code, 'channel_code' => $channelCode, 'locale_code' => $localeCode]
            )->fetch();
            if (!isset($row['code'])) {
                continue;
            }

            $rawValues = json_decode($row['raw_values'], true);
            $filteredRawValues = array_intersect_key($rawValues, [$row['attribute_code'] => true]);

            $productModels[$row['code']] = $filteredRawValues;
            $productModelsInfo[$row['code']]['is_scopable'] = $row['is_scopable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['is_localizable'] = $row['is_localizable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['attribute_code'] = $row['attribute_code'];
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($productModels);

        foreach ($valueCollections as $productModelCode => $valueCollection) {
            $productModelInfo = $productModelsInfo[$productModelCode];

            $images[$productModelCode]['image'] = $valueCollection->getByCodes(
                $productModelInfo['attribute_code'],
                $productModelInfo['is_scopable'],
                $productModelInfo['is_localizable']
            );
        }

        return $images;
    }

    /**
     * It gets image from the oldest child variant product having a non null image.
     *
     * As we can't get easily the first row of each group when doing a group by,
     * it executes one request per product model.
     *
     * @param array  $codes
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     */
    private function getImagesFromVariantProduct(array $codes, string $channelCode, string $localeCode): array
    {
        $images = [];
        foreach ($codes as $code) {
            $images[$code]['image'] = null;
        }

        $sql = <<<SQL
            SELECT 
                pm_root.code,
                a_image.code as attribute_code,
                a_image.is_localizable,
                a_image.is_scopable,
                product_child.raw_values,
                JSON_EXTRACT(
                    product_child.raw_values,
                    CONCAT('$."', a_image.code, '".', IF(is_scopable = 1, '":channel_code"', '"<all_channels>"'), '.', IF(is_localizable = 1, '":locale_code"', '"<all_locales>"'))
                ) as image_value
            FROM
                pim_catalog_product_model pm_root
                LEFT JOIN pim_catalog_product_model pm_child ON pm_child.parent_id = pm_root.id
                JOIN pim_catalog_product product_child ON product_child.product_model_id = COALESCE(pm_child.id, pm_root.id)
                JOIN pim_catalog_family_variant fv ON fv.id = pm_root.family_variant_id
                JOIN pim_catalog_family f ON f.id = fv.family_id
                JOIN pim_catalog_attribute a_image ON a_image.id = f.image_attribute_id
            WHERE
                pm_root.code = :code
            HAVING
                image_value IS NOT NULL AND JSON_TYPE(image_value) != 'NULL'
            ORDER BY 
                product_child.created ASC
            LIMIT 1
SQL;

        $productModels = [];
        $productModelsInfo = [];

        foreach ($codes as $code) {
            $row = $this->connection->executeQuery(
                $sql,
                ['code' => $code, 'channel_code' => $channelCode, 'locale_code' => $localeCode]
            )->fetch();

            if (!isset($row['code'])) {
                continue;
            }

            $rawValues = json_decode($row['raw_values'], true);
            $filteredRawValues = array_intersect_key($rawValues, [$row['attribute_code'] => true]);
            $productModels[$row['code']] = $filteredRawValues;
            $productModelsInfo[$row['code']]['is_scopable'] = $row['is_scopable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['is_localizable'] = $row['is_localizable'] ? $channelCode : null;
            $productModelsInfo[$row['code']]['attribute_code'] = $row['attribute_code'];
        }

        $valueCollections = $this->valueCollectionFactory->createMultipleFromStorageFormat($productModels);

        foreach ($valueCollections as $productModelCode => $valueCollection) {
            $productModelInfo = $productModelsInfo[$productModelCode];

            $images[$productModelCode]['image'] = $valueCollection->getByCodes(
                $productModelInfo['attribute_code'],
                $productModelInfo['is_scopable'],
                $productModelInfo['is_localizable']
            );
        }

        return $images;
    }
}
