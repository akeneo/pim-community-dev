<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register "flat to standard" array converters ordered by priority
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFlatToStandardConverterPass implements CompilerPassInterface
{
    /** @staticvar */
    const CONVERTER_REGISTRY = 'pim_connector.array_converter.flat_to_standard.product.value_converter.registry';

    /** @staticvar */
    const CONVERTER_TAG = 'pim_connector.array_converter.flat_to_standard.product.value_converter';

    /** @staticvar int The default priority in registry stack */
    const DEFAULT_PRIORITY = 100;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerConverters($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerConverters(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::CONVERTER_REGISTRY)) {
            return;
        }

        $registry = $container->getDefinition(static::CONVERTER_REGISTRY);
        $converters = $container->findTaggedServiceIds(static::CONVERTER_TAG);

        foreach ($converters as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $services[$priority][] = $serviceId;
            }
        }

        ksort($services);

        foreach ($services as $priority => $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $registry->addMethodCall('register', [new Reference($serviceId)]);
            }
        }
    }
}
