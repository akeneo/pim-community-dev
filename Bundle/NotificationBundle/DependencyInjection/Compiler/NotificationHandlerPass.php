<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class NotificationHandlerPass implements CompilerPassInterface
{
    const TAG         = 'notification.handler';
    const SERVICE_KEY = 'oro_notification.manager';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $serviceDefinition = $container->getDefinition(self::SERVICE_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        foreach ($taggedServices as $serviceId => $taggedService) {
            $serviceDefinition->addMethodCall('addHandler', array(new Reference($serviceId)));
        }
    }
}
