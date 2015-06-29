<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register tagged render type providers in the render type provider registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFieldProvidersPass implements CompilerPassInterface
{
    /** @staticvar int The default render type provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_enrich.provider.field.registry';

    /** @staticvar string */
    const FIELD_PROVIDER_TAG = 'pim_enrich.provider.field';

    /** @var ReferenceFactory */
    protected $factory;

    /**
     * @param ReferenceFactory $factory
     */
    public function __construct(ReferenceFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);

        $providers = [];
        foreach ($container->findTaggedServiceIds(static::FIELD_PROVIDER_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                if (!isset($providers[$priority])) {
                    $providers[$priority] = [];
                }

                $providers[$priority][] = $this->factory->createReference($serviceId);
            }
        }

        ksort($providers);
        foreach ($providers as $unsortedProviders) {
            foreach ($unsortedProviders as $provider) {
                $registryDefinition->addMethodCall('addProvider', [$provider]);
            }
        }
    }
}
