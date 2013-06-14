<?php

namespace Oro\Bundle\FormBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class SearchRegistryCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('oro_form.autocomplete.search_registry');

        foreach ($container->findTaggedServiceIds('oro_form.autocomplete.search_handler') as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                $name = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $definition->addMethodCall('addSearchHandler', array($name, new Reference($id)));
            }
        }
    }
}
