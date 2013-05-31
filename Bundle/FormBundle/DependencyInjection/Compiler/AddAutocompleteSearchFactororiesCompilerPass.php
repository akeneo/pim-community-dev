<?php

namespace Oro\Bundle\FormBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddAutocompleteSearchFactororiesCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('oro_form.autocomplete.search_registry');
        $factories = array();

        foreach ($container->findTaggedServiceIds('oro_form.autocomplete.search_factory') as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                $name = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $factories[$name] = new Reference($id);
            }
        }

        $definition->replaceArgument(1, $factories);
    }
}
