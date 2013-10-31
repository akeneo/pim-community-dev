<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    const DATAGRID_FILTER_TAG         = 'oro_grid.filter.type';
    const DATAGRID_FILTER_FACTORY_KEY = 'oro_grid.filter.factory';
    const DATAGRID_ACTION_TAG         = 'oro_grid.action.type';
    const DATAGRID_ACTION_FACTORY_KEY = 'oro_grid.action.factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, self::DATAGRID_FILTER_FACTORY_KEY, self::DATAGRID_FILTER_TAG);
        $this->injectEntityTypesByTag($container, self::DATAGRID_ACTION_FACTORY_KEY, self::DATAGRID_ACTION_TAG);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param string $tagName
     */
    protected function injectEntityTypesByTag(ContainerBuilder $container, $serviceId, $tagName)
    {
        $definition = $container->getDefinition($serviceId);
        $types      = array();

        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $container->getDefinition($id)->setScope(ContainerInterface::SCOPE_PROTOTYPE);

            foreach ($attributes as $eachTag) {
                $index = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $types[$index] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
