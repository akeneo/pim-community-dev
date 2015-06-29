<?php

namespace Pim\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register notification factories in a dedicated registry
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterNotificationFactoryPass implements CompilerPassInterface
{
    /** @staticvar integer */
    const DEFAULT_PRIORITY = 25;

    /** @staticvar string */
    const NOTIFICATION_FACTORY_REGISTRY = 'pim_notification.registry.factory.notification';

    /** @staticvar string */
    const JOB_NOTIFICATION_FACTORY_TAG = 'pim_notification.factory.job_notification';

    public function process(ContainerBuilder $container)
    {
        $this->registerFactories($container);
    }

    /**
     * Add tagged factory services to the registry
     *
     * @param ContainerBuilder $container
     */
    protected function registerFactories(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::NOTIFICATION_FACTORY_REGISTRY)) {
            throw new \LogicException('Notification factory registry must be configured');
        }

        $registry = $container->getDefinition(self::NOTIFICATION_FACTORY_REGISTRY);

        $factories = $this->findAndSortTaggedServices(self::JOB_NOTIFICATION_FACTORY_TAG, $container);
        foreach ($factories as $factory) {
            $registry->addMethodCall('registerJobNotificationFactory', [$factory]);
        }
    }

    /**
     * Returns an array of service references for the specified tag name
     *
     * @param string           $tagName
     * @param ContainerBuilder $container
     *
     * @return Reference[]
     */
    protected function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);

        $sortedServices = [];
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?: self::DEFAULT_PRIORITY;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        return count($sortedServices) > 0 ? call_user_func_array('array_merge', $sortedServices) : [];
    }
}
