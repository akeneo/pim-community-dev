<?php

namespace Oro\Bundle\TagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TagManagerPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_tag.tag.manager';
    const TAG = 'oro_tag.tag_manager';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $tagAttributes) {
            $container->getDefinition($id)->addMethodCall('setTagManager', array(new Reference(self::SERVICE_KEY)));
        }
    }
}
