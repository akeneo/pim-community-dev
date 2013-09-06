<?php

namespace Oro\Bundle\EntityExtendBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

use Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler\EntityManagerPass;
use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;

class OroEntityExtendBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EntityManagerPass());

        $entityCacheDir = $container->getParameter('kernel.root_dir') . '/entities/Extend/Entity';
        if (!is_dir($entityCacheDir)) {
            if (false === @mkdir($entityCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create entity cache directory "%s".', $entityCacheDir));
            }
        }
        $proxyCacheDir = $container->getParameter('kernel.root_dir') . '/entities/Extend/Proxy';
        if (!is_dir($proxyCacheDir)) {
            if (false === @mkdir($proxyCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create proxy cache directory "%s".', $proxyCacheDir));
            }
        }

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array($entityCacheDir . '/EAV' => 'Extend\Entity\EAV',),
                array(),
                'oro_entity_extend.backend.eav'
            )
        );

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array($entityCacheDir . '/Dynamic' => 'Extend\Entity\Dynamic',),
                array(),
                'oro_entity_extend.backend.dynamic'
            )
        );
    }
}
