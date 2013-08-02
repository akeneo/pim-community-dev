<?php

namespace Oro\Bundle\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EmailAddressManagerPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_email.address.manager';
    const TAG = 'oro_email.owner.provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $definition = $container->getDefinition(self::SERVICE_KEY);

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        $providers = array();
        foreach ($taggedServices as $id => $tagAttributes) {
            $order = PHP_INT_MAX;
            foreach ($tagAttributes as $attributes) {
                if (!empty($attributes['order'])) {
                    $order = (int)$attributes['order'];
                    break;
                }
            }
            $providers[$order] = $id;
        }
        ksort($providers);
        foreach ($providers as $providerServiceId) {
            $definition->addMethodCall('addProvider', array(new Reference($providerServiceId)));
        }
    }
}
