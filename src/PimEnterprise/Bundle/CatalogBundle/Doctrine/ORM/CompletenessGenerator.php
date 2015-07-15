<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator as CommunityCompletenessGenerator;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\EnterpriseCompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Enterprise completeness generator
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CompletenessGenerator extends CommunityCompletenessGenerator implements EnterpriseCompletenessGeneratorInterface
{
    /** @staticvar string */
    const COMPLETE_ASSETS_TABLE = 'complete_asset';

    /** @var string FQCN of asset */
    protected $assetClass;

    /** @var AssetFinderInterface */
    protected $assetFinder;

    /**
     * @param EntityManagerInterface $manager
     * @param string                 $productClass
     * @param string                 $productValueClass
     * @param string                 $attributeClass
     * @param string                 $assetClass
     * @param AssetFinderInterface   $assetFinder
     */
    public function __construct(
        EntityManagerInterface $manager,
        $productClass,
        $productValueClass,
        $attributeClass,
        $assetClass,
        AssetFinderInterface $assetFinder
    ) {
        parent::__construct($manager, $productClass, $productValueClass, $attributeClass);

        $this->assetClass  = $assetClass;
        $this->assetFinder = $assetFinder;
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(array $criteria = [])
    {
        $this->prepareCompleteAssets($criteria);
        parent::generate($criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function getForeignKeysFromMappings($mappings)
    {
        $index = 0;

        $productForeignKeys = array_reduce(
            $mappings,
            function ($fields, $mapping) use (&$index) {
                $index++;
                if ($mapping['targetEntity'] == $this->assetClass) {
                    return $fields;
                }

                return array_merge(
                    $fields,
                    $this->getAssociationFields($mapping, $this->getAssociationAlias($index))
                );
            },
            []
        );

        return $productForeignKeys;
    }

    /**
     * Create temporary table for complete assets collection attributes
     * An assets collection is complete on a locale/channel
     * if there is at least one varaition file for the locale/channel tuple
     *
     * @param string[] $criteria
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareCompleteAssets(array $criteria)
    {
        $cleanupSql  = "DROP TABLE IF EXISTS " . self::COMPLETE_ASSETS_TABLE . PHP_EOL;
        $cleanupStmt = $this->connection->prepare($cleanupSql);
        $cleanupStmt->execute();

        $selectSql = 'SELECT value_id, locale_id, channel_id
            FROM
            (
                SELECT av.value_id,
                IF (r.locale_id IS NOT NULL, r.locale_id, cl.locale_id) AS locale_id,
                v.channel_id,
                v.file_id, a.code
                FROM pim_catalog_product_value_asset av
                JOIN pim_catalog_product_value pv ON av.value_id = pv.id
                JOIN pimee_product_asset_asset a ON av.asset_id = a.id
                JOIN pimee_product_asset_reference r ON r.asset_id = a.id
                JOIN pimee_product_asset_variation v ON v.reference_id = r.id
                LEFT JOIN pim_catalog_channel_locale AS cl ON v.channel_id = cl.channel_id AND r.locale_id IS NULL
                WHERE 1 = 1
                %product_value_conditions%
                %channel_conditions%
            ) AS unionTable

            GROUP BY value_id, locale_id, channel_id

            HAVING COUNT(file_id) > 0';

        $selectSql = $this->applyCriteria($selectSql, $criteria);

        $createPattern = 'CREATE TEMPORARY TABLE %s (value_id INT, locale_id INT, channel_id INT) %s';

        $createSql = sprintf($createPattern, self::COMPLETE_ASSETS_TABLE, $selectSql);

        $stmt = $this->connection->prepare($createSql);

        foreach ($criteria as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSkippedMappings()
    {
        $skippedByParent = parent::getSkippedMappings();

        return array_merge($skippedByParent, ['assets']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraJoins()
    {
        $assetsJoin = 'LEFT JOIN %s AS complete_asset
            ON complete_asset.value_id = v.id
            AND complete_asset.channel_id = c.id
            AND complete_asset.locale_id = l.id';

        $assetsJoin = sprintf($assetsJoin, static::COMPLETE_ASSETS_TABLE);
        $extraJoins = array_merge(parent::getExtraJoins(), [$assetsJoin]);

        return $extraJoins;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraConditions()
    {
        $assetsConditions = sprintf('OR %s.value_id IS NOT NULL', static::COMPLETE_ASSETS_TABLE);
        $extraConditions  = array_merge(parent::getExtraConditions(), [$assetsConditions]);

        return $extraConditions;
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForAsset(AssetInterface $asset)
    {
        $products = $this->assetFinder->retrieveAssetProducts($asset);

        foreach ($products as $product) {
            $this->schedule($product);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria($sql, $criteria)
    {
        $sql = parent::applyCriteria($sql, $criteria);

        $productValueCondition = '';

        if (array_key_exists('productId', $criteria)) {
            $productValueCondition = 'AND pv.entity_id = :productId';
        }

        $sql = str_replace('%product_value_conditions%', $productValueCondition, $sql);

        return $sql;
    }
}
