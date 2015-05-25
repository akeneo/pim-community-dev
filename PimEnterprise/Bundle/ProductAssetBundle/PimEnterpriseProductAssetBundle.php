<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle;

use PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler\RegisterMetadataBuildersPass;
use PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Product asset management bundle
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class PimEnterpriseProductAssetBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterMetadataBuildersPass())
            ->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'PimEnterprise\Component\ProductAsset\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                'akeneo_storage_utils.storage_driver.doctrine/orm'
            )
        );
    }
}
