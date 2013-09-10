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

        $cacheDirs = array(
            $container->getParameter('kernel.root_dir') . '/entities/Extend/Base',
            $container->getParameter('kernel.root_dir') . '/entities/Extend/Entity',
            $container->getParameter('kernel.root_dir') . '/entities/Extend/Backup',
            $container->getParameter('kernel.root_dir') . '/entities/Extend/Validator',
        );

        foreach ($cacheDirs as $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
        }

        /*$container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array(
                    $container->getParameter('kernel.root_dir') . '/entities/Extend/Entity' => 'Extend\Entity'
                )
            )
        );*/
    }
}
