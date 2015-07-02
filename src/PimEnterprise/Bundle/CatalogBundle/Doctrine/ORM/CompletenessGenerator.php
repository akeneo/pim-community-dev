<?php

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator as CommunityCompletenessGenerator;

class CompletenessGenerator extends CommunityCompletenessGenerator
{
    /** @staticvar string */
    const COMPLETE_ASSETS_TABLE = 'complete_asset';

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

    /**
     * {@inheritdoc}
     */
    protected function generate(array $criteria = [])
    {
        $this->prepareCompleteAssets();
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
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareCompleteAssets()
    {
        $cleanupSql  = "DROP TABLE IF EXISTS " . self::COMPLETE_ASSETS_TABLE . PHP_EOL;
        $cleanupStmt = $this->connection->prepare($cleanupSql);
        $cleanupStmt->execute();

        $selectSql = 'SELECT value_id, locale_id, channel_id
            FROM
            (
                SELECT av.value_id, cl.locale_id , v.channel_id, v.file_id

                FROM pim_catalog_product_value_asset av

                JOIN pimee_product_asset_asset a ON av.asset_id = a.id
                JOIN pimee_product_asset_reference r ON r.asset_id = a.id
                JOIN pimee_product_asset_variation v ON v.reference_id = r.id
                JOIN pim_catalog_channel_locale AS cl ON v.channel_id = cl.channel_id

                WHERE r.locale_id IS NULL

            UNION ALL

                SELECT av.value_id, r.locale_id , v.channel_id, v.file_id

                FROM pim_catalog_product_value_asset av

                JOIN pimee_product_asset_asset a ON av.asset_id = a.id
                JOIN pimee_product_asset_reference r ON r.asset_id = a.id
                JOIN pimee_product_asset_variation v ON v.reference_id = r.id

                WHERE r.locale_id IS NOT NULL
            ) AS unionTable

            GROUP BY value_id, locale_id, channel_id

            HAVING COUNT(file_id) > 0';

        $createPattern = 'CREATE TEMPORARY TABLE %s (value_id INT, locale_id INT, channel_id INT) %s';

        $createSql = sprintf($createPattern, self::COMPLETE_ASSETS_TABLE, $selectSql);

        $stmt = $this->connection->prepare($createSql);
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
}
