<?php

namespace Oro\Bundle\FormBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class SearchFactoryCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('oro_form.autocomplete.search_factory');

        foreach ($container->findTaggedServiceIds('oro_form.autocomplete.search_factory') as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                $name = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $definition->addMethodCall('addSearchFactory', array($name, new Reference(new Reference($id))));
            }
        }
    }
}
