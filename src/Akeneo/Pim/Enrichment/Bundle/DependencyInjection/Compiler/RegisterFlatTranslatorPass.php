<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterFlatTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $flatPropertyValueTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.property_value_translator_registry');
        $flatPropertyValueTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.property_value_translator');
        foreach (array_keys($flatPropertyValueTranslators) as $flatPropertyValueTranslatorId) {
            $flatPropertyValueTranslatorRegistry->addMethodCall(
                'addTranslator',
                [new Reference($flatPropertyValueTranslatorId)]
            );
        }

        $flatAttributeValueTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.attribute_value_translator_registry');
        $flatAttributeValueTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.attribute_value_translator');
        foreach (array_keys($flatAttributeValueTranslators) as $flatAttributeValueTranslatorId) {
            $flatAttributeValueTranslatorRegistry->addMethodCall(
                'addTranslator',
                [new Reference($flatAttributeValueTranslatorId)]
            );
        }

        $flatHeaderTranslatorRegistry = $container->getDefinition('pim_connector.flat_translators.header_translator_registry');
        $flatHeaderTranslators = $container->findTaggedServiceIds('pim_connector.flat_translators.header_translator');
        foreach (array_keys($flatHeaderTranslators) as $flatHeaderTranslatorId) {
            $flatHeaderTranslatorRegistry->addMethodCall(
                'addTranslator',
                [new Reference($flatHeaderTranslatorId)]
            );
        }
    }
}
