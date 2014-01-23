<?php

namespace Pim\Bundle\CustomEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds value readers to the product reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationRegistryPass implements CompilerPassInterface
{
    /**
     * @staticvar string The id of the product reader service
     */
    const CONFIGURATION_REGISTRY_SERVICE = 'pim_custom_entity.configuration.registry';

    /**
     * @staticvar string The tag for FixtureBuilder services
     */
    const CONFIGURATION_TAG = 'pim_custom_entity.configuration';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::CONFIGURATION_REGISTRY_SERVICE);
        foreach (array_keys($container->findTaggedServiceIds(self::CONFIGURATION_TAG)) as $serviceId) {
            $definition->addMethodCall('add', [new Reference($serviceId)]);
        }
    }
}
