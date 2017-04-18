<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register product query filters in a dedicated registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterProductQueryFilterPass implements CompilerPassInterface
{
    /** @staticvar integer */
    const DEFAULT_PRIORITY = 25;

    /** @staticvar string */
    const QUERY_FILTER_REGISTRY = 'pim_catalog.query.filter.registry';

    /** @staticvar string */
    const QUERY_FILTER_TAG = 'pim_catalog.elasticsearch.query.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerFilters($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerFilters(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::QUERY_FILTER_REGISTRY)) {
            throw new \LogicException('Filter registry must be configured');
        }

        $registry = $container->getDefinition(self::QUERY_FILTER_REGISTRY);

        $filters = $this->findAndSortTaggedServices(self::QUERY_FILTER_TAG, $container);
        foreach ($filters as $filter) {
            $registry->addMethodCall('register', [$filter]);
        }
    }

    /**
     * Returns an array of service references for a specified tag name
     *
     * @param string           $tagName
     * @param ContainerBuilder $container
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);

        $sortedServices = [];
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : self::DEFAULT_PRIORITY;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        return count($sortedServices) > 0 ? call_user_func_array('array_merge', $sortedServices) : [];
    }
}
