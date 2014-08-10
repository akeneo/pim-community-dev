<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register product query filters and sorters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterProductQueryBuilderPass implements CompilerPassInterface
{
    /** @staticvar integer */
    const DEFAULT_PRIORITY = 25;

    /** @staticvar string */
    const QUERY_BUILDER_SERVICE = 'pim_catalog.doctrine.product_query_builder';

    /** @staticvar string */
    const QUERY_FILTER_TAG = 'pim_catalog.doctrine.query.filter';

    /** @staticvar string */
    const QUERY_SORTER_TAG = 'pim_catalog.doctrine.query.sorter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::QUERY_BUILDER_SERVICE)) {
            throw new \LogicException('ProductQueryBuilder must be configured');
        }

        $service = $container->getDefinition(self::QUERY_BUILDER_SERVICE);

        $filters = $this->findAndSortTaggedServices(self::QUERY_FILTER_TAG, $container);
        foreach ($filters as $filter) {
            $service->addMethodCall('registerFilter', [$filter]);
        }

        $sorters = $this->findAndSortTaggedServices(self::QUERY_SORTER_TAG, $container);
        foreach ($sorters as $sorter) {
            $service->addMethodCall('registerSorter', [$sorter]);
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

        $sortedServices = array();
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : self::DEFAULT_PRIORITY;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        return call_user_func_array('array_merge', $sortedServices);
    }
}
