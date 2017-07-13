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
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator as BaseCompletenessGenerator;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Enterprise completeness generator
 * Override of base generator to integrate assets in the completeness process
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CompletenessGenerator extends BaseCompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @staticvar string */
    const COMPLETE_ASSETS_TABLE = 'complete_asset';

    /** @var string FQCN of asset */
    protected $assetClass;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /**
     * @param EntityManagerInterface   $manager
     * @param AssetRepositoryInterface $assetRepository
     * @param string                   $productClass
     * @param string                   $productValueClass
     * @param string                   $attributeClass
     * @param string                   $assetClass
     * @param int                      $commitBatchSize
     */
    public function __construct(
        EntityManagerInterface $manager,
        AssetRepositoryInterface $assetRepository,
        $productClass,
        $productValueClass,
        $attributeClass,
        $assetClass,
        $commitBatchSize = 1000
    ) {
        parent::__construct($manager, $productClass, $productValueClass, $attributeClass, $commitBatchSize);

        $this->assetRepository = $assetRepository;
        $this->assetClass      = $assetClass;
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForAsset(AssetInterface $asset)
    {
        $products = $this->assetRepository->findProducts($asset);

        foreach ($products as $product) {
            $this->schedule($product);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(array $criteria = [])
    {
        if (!isset($criteria['productId'])) {
            $this->prepareMissingCompletenesses($criteria);

            $this->prepareCompletePrices($criteria);
            $this->prepareCompleteAssets($criteria);
        }

        $this->insertCompleteness($criteria);
    }

    /**
     * Create temporary table for complete assets collection attributes
     * An assets collection is complete on a locale/channel
     * if there is at least one variation file for the locale/channel tuple
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

        $tempTableName = self::COMPLETE_ASSETS_TABLE;
        $createSql = <<<SQL
CREATE TEMPORARY TABLE {$tempTableName} (value_id INT, locale_id INT, channel_id INT)
SQL;
        $createSql = $this->applyTableNames($createSql);
        $tempTableStmt = $this->connection->prepare($createSql);
        $tempTableStmt->execute();

        $sql = $this->getCompleteAssetsSQL();
        $sql = $this->applyCriteria($sql, $criteria);
        $sql = $this->applyTableNames($sql);

        $fetchStmt = $this->connection->prepare($sql);
        foreach ($criteria as $placeholder => $value) {
            $fetchStmt->bindValue($placeholder, $value);
        }
        $fetchStmt->execute();

        $insertSql = <<<SQL
    INSERT INTO {$tempTableName} (value_id, locale_id, channel_id) 
    VALUES (:value_id, :locale_id, :channel_id)
SQL;
        $insertStmt = $this->connection->prepare($insertSql);
        $count = 0;

        try {
            while ($completeness = $fetchStmt->fetch()) {
                if ($count === 0) {
                    $this->connection->beginTransaction();
                }

                $insertStmt->bindValue('value_id', $completeness['value_id']);
                $insertStmt->bindValue('locale_id', $completeness['locale_id']);
                $insertStmt->bindValue('channel_id', $completeness['channel_id']);
                $insertStmt->execute();

                if ($count === $this->commitBatchSize) {
                    $this->connection->commit();
                    $count = 0;
                }
            }

            if ($count > 0) {
                $this->connection->commit();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria($sql, $criteria)
    {
        $sql = parent::applyCriteria($sql, $criteria);

        $valueCondition = '';

        if (array_key_exists('productId', $criteria)) {
            $valueCondition = 'AND pv.entity_id = :productId';
        }

        $sql = str_replace('%product_value_conditions%', $valueCondition, $sql);

        return $sql;
    }

    /**
     * Overrided method to exclude assets from automatic mapping
     *
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
    protected function getExtraJoins(array $criteria)
    {
        $completeAssetTable = isset($criteria['productId']) ?
            $this->getCompleteAssetsSQL() :
            self::COMPLETE_ASSETS_TABLE;

        $assetsJoin = 'LEFT JOIN %s AS complete_asset
            ON complete_asset.value_id = v.id
            AND complete_asset.channel_id = c.id
            AND complete_asset.locale_id = l.id';

        $assetsJoin = sprintf($assetsJoin, $completeAssetTable);

        return array_merge(parent::getExtraJoins($criteria), [$assetsJoin]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraConditions(array $criteria)
    {
        return array_merge(
            parent::getExtraConditions($criteria),
            ['OR complete_asset.value_id IS NOT NULL']
        );
    }

    /**
     * Provide the SQL that allows to find complete assets.
     *
     * An assets collection is complete on a locale/channel
     * if there is at least one variation file for the locale/channel tuple.
     *
     * @return string
     */
    protected function getCompleteAssetsSQL()
    {
        return <<<COMPLETE_ASSETS_SQL
            (SELECT av.value_id,
                IF (r.locale_id IS NOT NULL, r.locale_id, cl.locale_id) AS locale_id,
                v.channel_id
            FROM pim_catalog_product_value_asset av
            JOIN pim_catalog_product_value pv ON av.value_id = pv.id
            JOIN pimee_product_asset_asset a ON av.asset_id = a.id
            JOIN pimee_product_asset_reference r ON r.asset_id = a.id
            JOIN pimee_product_asset_variation v ON v.reference_id = r.id
            LEFT JOIN pim_catalog_channel_locale AS cl ON v.channel_id = cl.channel_id AND r.locale_id IS NULL
            WHERE 1 = 1
                %product_value_conditions%
            GROUP BY value_id, locale_id, channel_id
            HAVING COUNT(v.file_info_id) > 0)
COMPLETE_ASSETS_SQL;
    }
}
