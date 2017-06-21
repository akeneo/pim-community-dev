<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that registers product value factories inside their main factory.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterValueFactoryPass implements CompilerPassInterface
{
    const DEFAULT_PRIORITY = 25;

    const PRODUCT_VALUE_FACTORY = 'pim_catalog.factory.value';

    const PRODUCT_VALUE_FACTORY_TAG = 'pim_catalog.factory.value';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $containerBuilder)
    {
        if (!$containerBuilder->hasDefinition(static::PRODUCT_VALUE_FACTORY)) {
            throw new \LogicException('Product value factory must be configured');
        }

        $factory = $containerBuilder->getDefinition(static::PRODUCT_VALUE_FACTORY);

        $filters = $this->findSortedTaggedServices(static::PRODUCT_VALUE_FACTORY_TAG, $containerBuilder);
        foreach ($filters as $filter) {
            $factory->addMethodCall('registerFactory', [$filter]);
        }
    }

    /**
     * Returns a sorted array of service references for a specified tag name.
     *
     * @param string           $tagName
     * @param ContainerBuilder $containerBuilder
     *
     * @return Reference[]
     */
    protected function findSortedTaggedServices($tagName, ContainerBuilder $containerBuilder)
    {
        $services = $containerBuilder->findTaggedServiceIds($tagName);

        if (empty($services)) {
            throw new \RuntimeException(sprintf(
                'You must tag at least one service as "%s" to use the product value factory service',
                $tagName
            ));
        }

        $sortedServices = [];
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }
        krsort($sortedServices);

        return count($sortedServices) > 0 ? call_user_func_array('array_merge', $sortedServices) : [];
    }
}
