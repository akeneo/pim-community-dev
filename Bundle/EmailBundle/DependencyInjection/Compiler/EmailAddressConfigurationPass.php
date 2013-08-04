<?php

namespace Oro\Bundle\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EmailAddressConfigurationPass implements CompilerPassInterface
{
    const EMAIL_ADDRESS_MANAGER_SERVICE_KEY = 'oro_email.email.address.manager';
    const EMAIL_OWNER_PROVIDER_SERVICE_KEY = 'oro_email.email.owner.provider';
    const TAG = 'oro_email.owner.provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $emailAddressManagerDefinition = null;
        $emailOwnerProviderDefinition = null;
        if ($container->hasDefinition(self::EMAIL_ADDRESS_MANAGER_SERVICE_KEY)) {
            $emailAddressManagerDefinition = $container->getDefinition(self::EMAIL_ADDRESS_MANAGER_SERVICE_KEY);
        }
        if ($container->hasDefinition(self::EMAIL_OWNER_PROVIDER_SERVICE_KEY)) {
            $emailOwnerProviderDefinition = $container->getDefinition(self::EMAIL_OWNER_PROVIDER_SERVICE_KEY);
        }
        if ($emailAddressManagerDefinition === null && $emailOwnerProviderDefinition === null)
        {
            return;
        }

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
            if ($emailAddressManagerDefinition !== null) {
                $emailAddressManagerDefinition->addMethodCall('addProvider', array(new Reference($providerServiceId)));
            }
            if ($emailOwnerProviderDefinition !== null) {
                $emailOwnerProviderDefinition->addMethodCall('addProvider', array(new Reference($providerServiceId)));
            }
        }
    }
}
