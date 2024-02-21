<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged data collector in the data collector registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterDataCollectorPass implements CompilerPassInterface
{
    const REGISTRY_ID = 'pim_analytics.data_collector.chained';
    const COLLECTOR_TAG = 'pim_analytics.data_collector';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);
        $services = $container->findTaggedServiceIds(static::COLLECTOR_TAG);
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $registryDefinition->addMethodCall(
                    'addCollector',
                    [
                        new Reference($serviceId),
                        $tag['type']
                    ]
                );
            }
        }
    }
}
