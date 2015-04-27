<?php

namespace DamEnterprise\Bundle\AssetBundle;

use DamEnterprise\Bundle\AssetBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DamEnterpriseAssetBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'DamEnterprise\Component\Asset\Model'
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
