<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterFlatTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $propertyTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.property_translator_registry');
        $propertyTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.property_translator');
        foreach (array_keys($propertyTranslators) as $propertyTranslatorId) {
            $propertyTranslatorRegistry->addMethodCall('addTranslator', [new Reference($propertyTranslatorId)]);
        }

        $attributeTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.attribute_translator_registry');
        $attributeTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.attribute_translator');
        foreach (array_keys($attributeTranslators) as $attributeTranslatorId) {
            $attributeTranslatorRegistry->addMethodCall('addTranslator', [new Reference($attributeTranslatorId)]);
        }

        $headerTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.header_translator_registry');
        $headerTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.header_translator');
        foreach (array_keys($headerTranslators) as $headerTranslatorId) {
            $headerTranslatorRegistry->addMethodCall('addTranslator', [new Reference($headerTranslatorId)]);
        }
    }
}
