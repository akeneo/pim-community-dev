<?php

namespace Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add grid filter types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddFilterTypesPass implements CompilerPassInterface
{
    /** @staticvar string */
    const FILTER_EXTENSION_ID = 'pim_datagrid.extension.filter';

    /** @staticvar string */
    const TAG_NAME = 'oro_filter.extension.orm_filter.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extension = $container->getDefinition(self::FILTER_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            $extension->addMethodCall('addFilter', [$tagAttrs['type'], new Reference($serviceId)]);
        }
    }
}
