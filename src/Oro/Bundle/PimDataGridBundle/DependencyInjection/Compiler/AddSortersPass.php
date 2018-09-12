<?php

namespace Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add grid sorters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSortersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const SORTER_EXTENSION_ID = 'pim_datagrid.extension.sorter';

    /** @staticvar string */
    const TAG_NAME = 'pim_datagrid.extension.sorter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extension = $container->getDefinition(self::SORTER_EXTENSION_ID);

        $filters = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($filters as $serviceId => $tags) {
            $tagAttrs = reset($tags);
            if (isset($tagAttrs['type']) === false) {
                throw new \InvalidArgumentException(
                    sprintf('The service %s must be configured with a type attribute', $serviceId)
                );
            }
            $extension->addMethodCall('addSorter', [$tagAttrs['type'], new Reference($serviceId)]);
        }
    }
}
