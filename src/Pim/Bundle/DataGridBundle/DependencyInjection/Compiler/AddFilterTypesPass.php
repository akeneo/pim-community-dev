<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

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
class AddFilterTypesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const FILTER_ORM_EXTENSION_ID = 'pim_datagrid.extension.filter.orm_filter';

    /**
     * @var string
     */
    const FILTER_PRODUCT_EXTENSION_ID = 'pim_datagrid.extension.filter.product_filter';

    /**
     * @®ar string
     */
    const TAG_NAME = 'oro_filter.extension.orm_filter.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ormExtension = $container->getDefinition(self::FILTER_ORM_EXTENSION_ID);
        $productExtension = $container->getDefinition(self::FILTER_PRODUCT_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if ($ormExtension) {
                $ormExtension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
            }
            if ($productExtension) {
                $productExtension->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
