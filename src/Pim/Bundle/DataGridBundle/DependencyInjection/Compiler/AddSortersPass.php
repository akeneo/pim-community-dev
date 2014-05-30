<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid sorters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSortersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const SORTER_EXTENSION_ID = 'pim_datagrid.extension.sorter.orm_sorter';

    /**
     * @var string
     */
    const SORTER_PRODUCT_EXTENSION_ID = 'pim_datagrid.extension.sorter.product_sorter';

    /**
     * @var string
     */
    const TAG_NAME = 'pim_datagrid.extension.sorter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ormExtension = $container->getDefinition(self::SORTER_EXTENSION_ID);
        $productExtension = $container->getDefinition(self::SORTER_PRODUCT_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if (isset($tagAttrs['type']) === false) {
                throw new \InvalidArgumentException(
                    sprintf('The service %s must be configured with a type attribute', $serviceId)
                );
            }
            if ($ormExtension) {
                $ormExtension->addMethodCall('addSorter', array($tagAttrs['type'], new Reference($serviceId)));
            }
            if ($productExtension) {
                $productExtension->addMethodCall('addSorter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
