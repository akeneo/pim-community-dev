<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterVersionPurgerAdvisorPass implements CompilerPassInterface
{
    const DEFAULT_PRIORITY = 100;

    const REGISTRY_ID = 'pim_versioning.purger.version';

    const ADVISOR_TAG_NAME = 'pim_versioning.purger.advisor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        $taggedServices = $container->findTaggedServiceIds(self::ADVISOR_TAG_NAME);

        $services = [];

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $services[$priority][] = $serviceId;
            }
        }

        ksort($services);

        foreach ($services as $priority => $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $registryDefinition->addMethodCall('addVersionPurgerAdvisor', [new Reference($serviceId)]);
            }
        }
    }
}
