<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
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
            'PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface' => 'pimee_product_asset.model.asset_reference.class',
            'PimEnterprise\Component\ProductAsset\Model\FileInterface' => 'pimee_product_asset.model.file.class',
            'PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface' => 'pimee_product_asset.model.file_metadata.interface',
            'PimEnterprise\Component\ProductAsset\Model\ImageMetadataInterface' => 'pimee_product_asset.model.image_metadata.interface'
        ];
    }
}
