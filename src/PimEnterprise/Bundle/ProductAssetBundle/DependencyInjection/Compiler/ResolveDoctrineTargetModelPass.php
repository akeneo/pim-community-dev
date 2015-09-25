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
            'PimEnterprise\Component\ProductAsset\Model\AssetInterface'         =>
                'pimee_product_asset.model.asset.class',
            'PimEnterprise\Component\ProductAsset\Model\VariationInterface'     =>
                'pimee_product_asset.model.variation.class',
            'PimEnterprise\Component\ProductAsset\Model\ReferenceInterface'     =>
                'pimee_product_asset.model.reference.class',
            'PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface'  =>
                'pimee_product_asset.model.file_metadata.class',
            'PimEnterprise\Component\ProductAsset\Model\ImageMetadataInterface' =>
                'pimee_product_asset.model.image_metadata.class',
            'PimEnterprise\Component\ProductAsset\Model\CategoryInterface'      =>
                'pimee_product_asset.model.category.class',
            'PimEnterprise\Component\ProductAsset\Model\TagInterface'           =>
                'pimee_product_asset.model.tag.class'
        ];
    }
}
