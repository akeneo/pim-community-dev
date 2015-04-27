<?php

namespace PamEnterprise\Bundle\AssetManagementBundle\DependencyInjection\Compiler;

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
            'PamEnterprise\Component\ProductAsset\Model\ProductAssetInterface' => 'pam_asset_management.model.asset.class',
            'PamEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface' => 'pam_asset_management.model.asset_variation.class',
        ];
    }
}
