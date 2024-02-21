<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged filters to the chained filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFilterPass implements CompilerPassInterface
{
    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_catalog.filter.chained';

    /** @staticvar string */
    const COLLECTION_FILTER_TAG = 'pim_catalog.filter.collection';

    /** @staticvar string */
    const OBJECT_FILTER_TAG = 'pim_catalog.filter.object';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(static::COLLECTION_FILTER_TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $registryDefinition->addMethodCall(
                    'addCollectionFilter',
                    [
                        new Reference($serviceId),
                        $attribute['type']
                    ]
                );
            }
        }

        foreach ($container->findTaggedServiceIds(static::OBJECT_FILTER_TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $registryDefinition->addMethodCall('addObjectFilter', [new Reference($serviceId), $attribute['type']]);
            }
        }
    }
}
