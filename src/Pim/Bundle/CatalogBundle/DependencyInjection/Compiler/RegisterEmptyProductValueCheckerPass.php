<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged empty value checker in the empty product value remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterEmptyProductValueCheckerPass implements CompilerPassInterface
{
    /** @staticvar int The default provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_catalog.updater.product_purger';

    /** @staticvar string */
    const CHECKER_TAG = 'pim_catalog.updater.empty_product_value_checker';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);

        $checkers = [];
        foreach ($container->findTaggedServiceIds(static::CHECKER_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                if (!isset($checkers[$priority])) {
                    $checkers[$priority] = [];
                }

                $checkers[$priority][] = new Reference($serviceId);
            }
        }

        ksort($checkers);
        foreach ($checkers as $unsortedProviders) {
            foreach ($unsortedProviders as $checker) {
                $registryDefinition->addMethodCall('addEmptyProductValueChecker', [$checker]);
            }
        }
    }
}
