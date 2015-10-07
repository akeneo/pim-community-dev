<?php
namespace Oro\Bundle\NavigationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class MenuBuilderChainPass implements CompilerPassInterface
{
    const MENU_BUILDER_TAG = 'oro_menu.builder';
    const MENU_PROVIDER_KEY = 'oro_menu.builder_chain';
    const ITEMS_BUILDER_TAG = 'oro_navigation.item.builder';
    const ITEMS_PROVIDER_KEY = 'oro_navigation.item.factory';

    public function process(ContainerBuilder $container)
    {
        $this->processMenu($container);
        $this->processItems($container);
    }

    protected function processMenu(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MENU_PROVIDER_KEY)) {
            return;
        }

        $definition = $container->getDefinition(self::MENU_PROVIDER_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::MENU_BUILDER_TAG);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $addBuilderArgs = array(new Reference($id));

                if (!empty($attributes['alias'])) {
                    $addBuilderArgs[] = $attributes['alias'];
                }

                $definition->addMethodCall('addBuilder', $addBuilderArgs);
            }
        }
    }

    protected function processItems(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::ITEMS_PROVIDER_KEY)) {
            return;
        }

        $definition = $container->getDefinition(self::ITEMS_PROVIDER_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::ITEMS_BUILDER_TAG);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes['alias'])) {
                    continue;
                }

                $builderDefinition = $container->getDefinition($id);
                $builderDefinition->addArgument($attributes['alias']);

                $addBuilderArgs = array(new Reference($id));
                $definition->addMethodCall('addBuilder', $addBuilderArgs);
            }
        }
    }
}
