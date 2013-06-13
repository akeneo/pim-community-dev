<?php

namespace Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddConverterCompilerPass implements CompilerPassInterface
{
    const VALUE_CONVERTER_TAG              = 'oro_importexport.value_converter';
    const VALUE_CONVERTER_REGISTRY_SERVICE = 'oro_importexport.value_converter.registry';

    const ITEM_CONVERTER_TAG              = 'oro_importexport.item_converter';
    const ITEM_CONVERTER_REGISTRY_SERVICE = 'oro_importexport.item_converter.registry';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectConvertersByTag($container, self::VALUE_CONVERTER_REGISTRY_SERVICE, self::VALUE_CONVERTER_TAG);
        $this->injectConvertersByTag($container, self::ITEM_CONVERTER_REGISTRY_SERVICE, self::ITEM_CONVERTER_TAG);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param string $tagName
     */
    protected function injectConvertersByTag(ContainerBuilder $container, $serviceId, $tagName)
    {
        $serviceDefinition = $container->getDefinition($serviceId);
        $converters = array();

        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                $index = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $converters[$index] = new Reference($id);
            }
        }

        $serviceDefinition->replaceArgument(0, $converters);
    }
}
