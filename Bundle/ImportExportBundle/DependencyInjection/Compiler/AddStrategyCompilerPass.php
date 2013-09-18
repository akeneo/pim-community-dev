<?php

namespace Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class AddStrategyCompilerPass implements CompilerPassInterface
{
    const STRATEGY_REGISTRY_SERVICE = 'oro_importexport.strategy.registry';
    const IMPORT_STRATEGY_TAG       = 'oro_importexport.strategy.import';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $strategyRegistry = $container->getDefinition(self::STRATEGY_REGISTRY_SERVICE);

        $strategies = $container->findTaggedServiceIds(self::IMPORT_STRATEGY_TAG);

        foreach ($strategies as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->assertTagAttributes($serviceId, $tag, array('entity', 'alias'));

                $strategyRegistry->addMethodCall(
                    'addImportStrategy',
                    array($container->getDefinition($serviceId), $tag['entity'], $tag['alias'])
                );
            }
        }
    }

    /**
     * @param string $serviceId
     * @param array $tag
     * @param array $attributes
     * @throws LogicException
     */
    private function assertTagAttributes($serviceId, array $tag, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (empty($tag[$attribute])) {
                throw new LogicException(
                    sprintf('Import strategy tag for service "%s" must have attribute "%s"', $serviceId, $attribute)
                );
            }
        }
    }
}
