<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid filter types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterTypesPass implements CompilerPassInterface
{
    /**
     * @var string
     * TODO : use same filter for product datasource ?
     */
    protected $filterExtensionIds = array(
        'pim_catalog.grid.extension.filter.orm_filter',
        'pim_catalog.grid.extension.filter.orm_product_filter'
    );

    /**
     * @®ar string
     */
    const TAG_NAME            = 'oro_filter.extension.orm_filter.filter';

    /**
     * @var string
     */
    const PRODUCT_TAG_NAME    = 'oro_filter.extension.orm_product_filter.filter';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        /**
         * Find and add available filters to extension
         */
        foreach ($this->filterExtensionIds as $extensionId) {
            $extension = $container->getDefinition($extensionId);
            if ($extension) {
                // add base filters
                $filters = $container->findTaggedServiceIds(self::TAG_NAME);
                foreach ($filters as $serviceId => $tags) {
                    $tagAttrs = reset($tags);
                    $extension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
                }
                // add flexible filters
                $filters = $container->findTaggedServiceIds(self::PRODUCT_TAG_NAME);
                foreach ($filters as $serviceId => $tags) {
                    $tagAttrs = reset($tags);
                    $extension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
                }
            }
        }
    }
}
