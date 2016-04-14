<?php

namespace Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged default parameters to the registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterDefaultJobParametersPass implements CompilerPassInterface
{
    /** @staticvar int The default provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'akeneo_batch.job_parameters.default_registry';

    /** @staticvar string */
    const SERVICE_TAG = 'akeneo_batch.job_parameters.default';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        $providers = [];
        foreach ($container->findTaggedServiceIds(self::SERVICE_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                if (!isset($providers[$priority])) {
                    $providers[$priority] = [];
                }

                $providers[$priority][] = new Reference($serviceId);
            }
        }

        ksort($providers);
        foreach ($providers as $unsortedProviders) {
            foreach ($unsortedProviders as $provider) {
                $registryDefinition->addMethodCall('register', [$provider]);
            }
        }
    }
}
