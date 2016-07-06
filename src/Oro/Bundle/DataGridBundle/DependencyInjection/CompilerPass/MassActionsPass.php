<?php

namespace Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MassActionsPass implements CompilerPassInterface
{
    const MASS_ACTION_EXTENSION_ID = 'oro_datagrid.extension.mass_action';
    const TAG_NAME = 'oro_datagrid.extension.mass_action.type';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        /**
         * Find and add available action types to action extension
         */
        $extension = $container->getDefinition(self::MASS_ACTION_EXTENSION_ID);
        if ($extension) {
            $actions = $container->findTaggedServiceIds(self::TAG_NAME);
            foreach ($actions as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $extension->addMethodCall('registerAction', [$tagAttrs['type'], $serviceId]);
            }
        }
    }
}
