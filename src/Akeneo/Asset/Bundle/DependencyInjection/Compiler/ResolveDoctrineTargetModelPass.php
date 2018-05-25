<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Bundle\DependencyInjection\Compiler;

use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

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
            'Akeneo\Asset\Component\Model\AssetInterface'         => 'pimee_product_asset.model.asset.class',
            'Akeneo\Asset\Component\Model\VariationInterface'     => 'pimee_product_asset.model.variation.class',
            'Akeneo\Asset\Component\Model\ReferenceInterface'     => 'pimee_product_asset.model.reference.class',
            'Akeneo\Asset\Component\Model\FileMetadataInterface'  => 'pimee_product_asset.model.file_metadata.class',
            'Akeneo\Asset\Component\Model\ImageMetadataInterface' => 'pimee_product_asset.model.image_metadata.class',
            'Akeneo\Asset\Component\Model\CategoryInterface'      => 'pimee_product_asset.model.category.class',
            'Akeneo\Asset\Component\Model\TagInterface'           => 'pimee_product_asset.model.tag.class'
        ];
    }
}
