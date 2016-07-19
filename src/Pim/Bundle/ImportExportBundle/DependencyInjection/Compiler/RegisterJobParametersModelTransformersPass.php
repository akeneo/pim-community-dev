<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register tagged forms model transformers to the registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegisterJobParametersModelTransformersPass implements CompilerPassInterface
{
    const DEFAULT_PRIORITY = 100;

    const REGISTRY_ID = 'pim_import_export.job_parameters.model_transformer_provider_registry';

    const SERVICE_TAG = 'pim_import_export.job_parameters.model_transformer_provider';

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
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        $providers = [];
        foreach ($container->findTaggedServiceIds(self::SERVICE_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $providers[$priority][] = $this->factory->createReference($serviceId);
            }
        }

        ksort($providers);
        foreach ($providers as $sortedProviders) {
            foreach ($sortedProviders as $provider) {
                $registryDefinition->addMethodCall('register', [$provider]);
            }
        }
    }
}
