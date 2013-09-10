<?php

namespace Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * CompilerPass to add flexible manager to connector
 */
class AddManagerCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_flexibleentity.registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('oro_flexibleentity.registry');
        $taggedManagerServices = $container->findTaggedServiceIds('oro_flexibleentity_manager');

        foreach ($taggedManagerServices as $managerId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $registryDefinition->addMethodCall(
                    'addManager',
                    array($managerId, new Reference($managerId), $attributes['entity'])
                );
            }
        }
    }
}
