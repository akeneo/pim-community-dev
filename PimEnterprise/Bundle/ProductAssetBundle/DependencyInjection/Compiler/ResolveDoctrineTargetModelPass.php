<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            'PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface' => 'pimee_product_asset.model.asset.class',
            'PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface' => 'pimee_product_asset.model.asset_variation.class',
            'PimEnterprise\Component\ProductAsset\Model\FileInterface' => 'pimee_product_asset.model.file.class',
        ];
    }
}
