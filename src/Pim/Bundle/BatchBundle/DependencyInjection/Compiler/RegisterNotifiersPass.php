<?php

namespace Pim\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register notifiers into the notification subscriber
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterNotifiersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('pim_batch.notification_subscriber')) {
            return;
        }

        $def = $container->getDefinition('pim_batch.notification_subscriber');
        foreach (array_keys($container->findTaggedServiceIds('pim_batch.notifier')) as $id) {
            $def->addMethodCall('registerNotifier', array(new Reference($id)));
        }
    }
}
