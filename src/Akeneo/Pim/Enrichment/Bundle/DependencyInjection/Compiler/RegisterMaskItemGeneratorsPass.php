<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register attributes complete checkers tagged services
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterMaskItemGeneratorsPass implements CompilerPassInterface
{
    /** @staticvar int The default render type provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string */
    const MASK_ITEM_GENERATOR = 'pim_catalog.completeness.mask_item_generator';

    /** @staticvar string */
    const SERVICE_TAG = 'pim_catalog.completeness.mask_item_generator.generator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MASK_ITEM_GENERATOR)) {
            return;
        }

        $service = $container->getDefinition(self::MASK_ITEM_GENERATOR);

        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        $services = [];

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                if (!isset($services[$priority])) {
                    $services[$priority] = [];
                }

                $services[$priority][] = $serviceId;
            }
        }

        ksort($services);

        foreach ($services as $priority => $unsortedServices) {
            foreach ($unsortedServices as $serviceId) {
                $service->addMethodCall('addGenerator', [new Reference($serviceId)]);
            }
        }
    }
}
