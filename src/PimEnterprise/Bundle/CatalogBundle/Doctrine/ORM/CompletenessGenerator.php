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
//    public function __construct(
//        EntityManagerInterface $manager,
//        $productClass,
//        $productValueClass,
//        $attributeClass,
//        $assetClass
//    ) {
//        parent::__construct($manager, $productClass, $productValueClass, $attributeClass);
//
//        $this->assetClass = $assetClass;
//    }

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
                if ($mapping['targetEntity'] == 'PimEnterprise\Component\ProductAsset\Model\Asset') {
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

        $assetsJoin = sprintf($assetsJoin, static::ASSETS_VALUES_TABLE);
        $extraJoins = array_merge(parent::getExtraJoins(), [$assetsJoin]);

        return $extraJoins;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraConditions()
    {
        $assetsConditions = sprintf('OR %s.value_id IS NOT NULL', static::ASSETS_VALUES_TABLE);
        $extraConditions  = array_merge(parent::getExtraConditions(), [$assetsConditions]);

        return $extraConditions;
    }
}
