<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator as CommunityCompletenessGenerator;

class CompletenessGenerator extends CommunityCompletenessGenerator
{
    /** @staticvar string */
    const ASSETS_VALUES_TABLE = 'complete_asset';

    /** @var string FQCN of asset */
    protected $assetClass;

    /**
     * @param EntityManagerInterface $manager
     * @param string                 $productClass
     * @param string                 $productValueClass
     * @param string                 $attributeClass
     * @param string                 $assetClass
     */
    public function __construct(
        EntityManagerInterface $manager,
        $productClass,
        $productValueClass,
        $attributeClass,
        $assetClass
    ) {
        parent::__construct($manager, $productClass, $productValueClass, $attributeClass);

        $this->assetClass = $assetClass;
    }

    protected function generate(array $criteria = [])
    {
        $this->prepareCompleteAssets();
        parent::generate($criteria);
    }

    /**
     * Provides the main SQL part
     *
     * @return string
     */
    protected function getMainSqlPart()
    {
        return <<<MAIN_SQL
            INSERT INTO pim_catalog_completeness (
                locale_id, channel_id, product_id, ratio, missing_count, required_count
            )
            SELECT
                l.id AS locale_id, c.id AS channel_id, p.id AS product_id,
                (
                    COUNT(distinct v.id)
                    / (
                        SELECT count(*)
                            FROM pim_catalog_attribute_requirement
                            WHERE family_id = p.family_id
                                AND channel_id = c.id
                                AND required = true
                    )
                    * 100
                ) AS ratio,
                (
                    (
                        SELECT count(*)
                            FROM pim_catalog_attribute_requirement
                            WHERE family_id = p.family_id
                                AND channel_id = c.id
                                AND required = true
                    ) - COUNT(distinct v.id)
                ) AS missing_count,
                (
                    SELECT count(*)
                        FROM pim_catalog_attribute_requirement
                        WHERE family_id = p.family_id
                            AND channel_id = c.id
                            AND required = true
                ) AS required_count
            FROM missing_completeness m
                JOIN pim_catalog_channel c ON c.id = m.channel_id
                JOIN pim_catalog_locale l ON l.id = m.locale_id
                JOIN %product_table% p ON p.id = m.product_id
                JOIN pim_catalog_attribute_requirement r ON r.family_id = p.family_id AND r.channel_id = c.id
                JOIN %product_value_table% v ON v.attribute_id = r.attribute_id
                    AND (v.scope_code = c.code OR v.scope_code IS NULL)
                    AND (v.locale_code = l.code OR v.locale_code IS NULL)
                    AND v.entity_id = p.id
                LEFT JOIN complete_price
                    ON complete_price.value_id = v.id
                    AND complete_price.channel_id = c.id
                    AND complete_price.locale_id = l.id
                LEFT JOIN complete_asset
                    ON complete_asset.value_id = v.id
                    AND complete_asset.channel_id = c.id
                    AND complete_asset.locale_id = l.id
                %product_value_joins%
            WHERE (%product_value_conditions% OR complete_price.value_id IS NOT NULL OR complete_asset.value_id IS NOT NULL) AND r.required = true
            GROUP BY p.id, c.id, l.id
MAIN_SQL;
    }

    /**
     * Returns an array of SQL conditions for the ProductValue entity
     *
     * @return array
     */
    protected function getProductValueConditions()
    {
        $index = 0;

        return array_map(
            function ($field) {
                return sprintf('%s IS NOT NULL', $field);
            },
            array_merge(
                $this->getClassContentFields($this->productValueClass, 'v'),
                array_reduce(
                    $this->getClassMetadata($this->productValueClass)->getAssociationMappings(),
                    function ($fields, $mapping) use (&$index) {
                        $index++;
                        if ($mapping['targetEntity'] == 'PimEnterprise\Component\ProductAsset\Model\Asset') {
                            return $fields;
                        }

                        return array_merge(
                            $fields,
                            $this->getAssociationFields($mapping, $this->getAssociationAlias($index))
                        );
                    },
                    []
                )
            )
        );
    }

    protected function prepareCompleteAssets()
    {
        $cleanupSql  = "DROP TABLE IF EXISTS " . self::ASSETS_VALUES_TABLE . PHP_EOL;
        $cleanupStmt = $this->connection->prepare($cleanupSql);
        $cleanupStmt->execute();

        $selectSql = 'SELECT av.value_id, r.locale_id , v.channel_id
                        FROM pim_catalog_product_value_asset av
                        JOIN pimee_product_asset_asset a ON av.asset_id = a.id
                        JOIN pimee_product_asset_reference r ON r.asset_id = a.id
                        JOIN pimee_product_asset_variation v ON v.reference_id = r.id
                        GROUP BY av.value_id, r.locale_id, v.channel_id
                        HAVING COUNT(v.file_id) > 0
                        ORDER BY channel_id, locale_id';

        $createPattern = 'CREATE TEMPORARY TABLE %s (value_id INT, locale_id INT, channel_id INT) %s';

        $createSql = sprintf($createPattern, self::ASSETS_VALUES_TABLE, $selectSql);

        $stmt = $this->connection->prepare($createSql);
        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSkippedMappings()
    {
        $skippedByParent = parent::getSkippedMappings();

        return array_merge($skippedByParent, ['asset']);
    }
}
