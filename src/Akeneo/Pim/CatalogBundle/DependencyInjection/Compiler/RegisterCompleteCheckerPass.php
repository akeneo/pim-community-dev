<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register attributes complete checkers tagged services
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterCompleteCheckerPass implements CompilerPassInterface
{
    /** @staticvar int The default render type provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string */
    const CHECKER = 'pim_catalog.completeness.checker';

    /** @staticvar string */
    const SERVICE_TAG = 'pim_catalog.completeness.checker.product_value';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHECKER)) {
            return;
        }

        $service = $container->getDefinition(self::CHECKER);

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
                $service->addMethodCall('addProductValueChecker', [new Reference($serviceId)]);
            }
        }
    }
}
