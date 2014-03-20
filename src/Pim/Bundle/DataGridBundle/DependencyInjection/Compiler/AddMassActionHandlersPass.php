<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add mass action handlers in registry
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddMassActionHandlersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const HANDLER_REGISTRY_SERVICE = 'pim_datagrid.extension.mass_action.handler.registry';

    /** @staticvar string */
    const HANDLER_EXTENSION_TAG    = 'pim_datagrid.extension.mass_action.handler';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $handlerRegistry = $container->getDefinition(self::HANDLER_REGISTRY_SERVICE);
        $taggedServices  = $container->findTaggedServiceIds(self::HANDLER_EXTENSION_TAG);

        foreach ($taggedServices as $serviceId => $tags) {
            $alias = (isset($tags[0]['alias'])) ? $tags[0]['alias'] : $serviceId;

            $handlerRegistry->addMethodCall('addHandler', array($alias, $serviceId));
        }
    }
}
