<?php

namespace Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged job parameters services to the relevant registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterJobParametersPass implements CompilerPassInterface
{
    /** @staticvar int The default provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'akeneo_batch.job.job_parameters.%s_registry';

    /** @staticvar string */
    const SERVICE_TAG = 'akeneo_batch.job.job_parameters.%s';

    /** @var string */
    protected $type;

    /**
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryId = sprintf(self::REGISTRY_ID, $this->type);
        if (!$container->hasDefinition($registryId)) {
            return;
        }

        $registryDefinition = $container->getDefinition($registryId);

        $providers = [];
        $serviceTag = sprintf(self::SERVICE_TAG, $this->type);
        foreach ($container->findTaggedServiceIds($serviceTag) as $serviceId => $tags) {
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
