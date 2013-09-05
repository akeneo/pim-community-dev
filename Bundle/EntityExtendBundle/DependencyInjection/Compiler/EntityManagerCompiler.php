<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EntityManagerCompiler implements CompilerPassInterface
{

    const EXTEND_MANAGER_SERVICE_KEY = 'oro_entity_extend.extend.extend_manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $em = $container->findDefinition('doctrine.orm.entity_manager');
        $em->addMethodCall('setExtendManager', array(new Reference(self::EXTEND_MANAGER_SERVICE_KEY)));
    }
}
