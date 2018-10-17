<?php

namespace Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler;

use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register tagged providers in the provider registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterGenericProvidersPass implements CompilerPassInterface
{
    /** @staticvar int The default provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_enrich.provider.%s.chained';

    /** @staticvar string */
    const PROVIDER_TAG = 'pim_enrich.provider.%s';

    /** @var ReferenceFactory */
    protected $factory;

    /** @var string */
    protected $providerType;

    /**
     * @param ReferenceFactory $factory
     * @param string           $providerType
     */
    public function __construct(ReferenceFactory $factory, $providerType)
    {
        $this->factory = $factory;
        $this->providerType = $providerType;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(sprintf(static::REGISTRY_ID, $this->providerType))) {
            return;
        }

        $registryDefinition = $container->getDefinition(sprintf(static::REGISTRY_ID, $this->providerType));

        $providers = [];
        foreach ($container->findTaggedServiceIds(sprintf(static::PROVIDER_TAG, $this->providerType)) as
            $serviceId => $tags) {
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
