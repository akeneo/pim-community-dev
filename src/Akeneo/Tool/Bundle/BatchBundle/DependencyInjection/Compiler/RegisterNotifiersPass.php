<?php

namespace Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register notifiers into the notification subscriber
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class RegisterNotifiersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('akeneo_batch.notification_subscriber')) {
            return;
        }

        $def = $container->getDefinition('akeneo_batch.notification_subscriber');
        foreach (array_keys($container->findTaggedServiceIds('akeneo_batch.notifier')) as $id) {
            $def->addMethodCall('registerNotifier', [new Reference($id)]);
        }
    }
}
