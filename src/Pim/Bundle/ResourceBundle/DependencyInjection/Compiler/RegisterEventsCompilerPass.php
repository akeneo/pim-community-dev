<?php

namespace Pim\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register resource events in the resource registry.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterEventsCompilerPass extends AbstractCompilerPass
{
    const DEFAULT_PRIORITY = 100;
    const RESOURCE_EVENT_TYPE_TAG = 'pim_resource.event';
    const RESOURCE_EVENT_REGISTRY_ID = 'pim_resource.event.type_registry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::RESOURCE_EVENT_REGISTRY_ID)) {
            throw new \LogicException('Resource event registry must be configured.');
        }

        $registry = $container->getDefinition(self::RESOURCE_EVENT_REGISTRY_ID);
        $eventTypes = $this->findAndSortTaggedServices(
            $container,
            self::RESOURCE_EVENT_TYPE_TAG,
            self::DEFAULT_PRIORITY
        );

        foreach ($eventTypes as $eventType) {
            $registry->addMethodCall('register', [$eventType]);
        }
    }
}
