<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register "standard to flat" array converters ordered by priority
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegisterStandardToFlatConverterPass implements CompilerPassInterface
{
    const CONVERTER_REGISTRY = 'pim_connector.array_converter.standard_to_flat.product.value_converter.registry';
    const CONVERTER_TAG = 'pim_connector.array_converter.standard_to_flat.product.value_converter';
    const DEFAULT_PRIORITY = 100;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::CONVERTER_REGISTRY)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::CONVERTER_REGISTRY);
        $converters = $container->findTaggedServiceIds(static::CONVERTER_TAG);

        foreach ($converters as $serviceId => $tags) {
            $this->registerConverter($registryDefinition, $tags, $serviceId);
        }
    }

    /**
     * @param Definition $registry
     * @param string[]   $tags
     * @param string     $serviceId
     */
    protected function registerConverter(Definition $registry, array $tags, $serviceId)
    {
        foreach ($tags as $tag) {
            $priority = isset($tag['priority']) ? (int)$tag['priority'] : static::DEFAULT_PRIORITY;
            $registry->addMethodCall(
                'register',
                [new Reference($serviceId), $priority]
            );
        }
    }
}
