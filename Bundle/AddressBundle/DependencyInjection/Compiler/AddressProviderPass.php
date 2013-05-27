<?php

namespace Oro\Bundle\AddressBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddressProviderPass implements CompilerPassInterface
{
    const PROVIDER_KEY = 'oro_address.address.provider';
    const TAG = 'oro_address.storage';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_KEY)) {
            return;
        }

        $definition = $container->getDefinition(self::PROVIDER_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        foreach ($taggedServices as $id => $tagAttributes) {
            $addStorageArgs = array(new Reference($id));
            foreach ($tagAttributes as $attributes) {
                if (!empty($attributes['alias'])) {
                    $addStorageArgs[] = $attributes['alias'];
                }

                $definition->addMethodCall('addStorage', $addStorageArgs);
            }
        }
    }
}
