<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterFlatTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->registerTranslators(
            $container,
            'pim_enrich.connector.flat_translators.property_value_registry',
            'pim_enrich.connector.flat_translators.property_value'
        );

        $this->registerTranslators(
            $container,
            'pim_enrich.connector.flat_translators.attribute_value_registry',
            'pim_enrich.connector.flat_translators.attribute_value'
        );

        $this->registerTranslators(
            $container,
            'pim_enrich.connector.flat_translators.header_registry',
            'pim_enrich.connector.flat_translators.header'
        );
    }

    private function registerTranslators(ContainerBuilder $container, string $registryId, string $translatorTag): void
    {
        $registry = $container->getDefinition($registryId);
        $translators = $container->findTaggedServiceIds($translatorTag);
        foreach (array_keys($translators) as $translatorId) {
            $registry->addMethodCall(
                'addTranslator',
                [new Reference($translatorId)]
            );
        }
    }
}
