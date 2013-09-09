<?php

namespace Oro\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register notifiers into the notification subscriber
 *
 */
class RegisterNotifiersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('oro_batch.notification_subscriber')) {
            return;
        }

        $def = $container->getDefinition('oro_batch.notification_subscriber');
        foreach (array_keys($container->findTaggedServiceIds('oro_batch.notifier')) as $id) {
            $def->addMethodCall('registerNotifier', array(new Reference($id)));
        }
    }
}
