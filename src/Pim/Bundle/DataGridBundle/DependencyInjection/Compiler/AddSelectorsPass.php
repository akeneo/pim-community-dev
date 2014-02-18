<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid selectorss
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSelectorsPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const SELECTOR_EXTENSION_ID = 'pim_datagrid.extension.selector.orm_selector';

    /**
     * @®ar string
     */
    const TAG_NAME = 'pim_datagrid.extension.selector';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extension = $container->getDefinition(self::SELECTOR_EXTENSION_ID);
        if ($extension) {
            $filters = $container->findTaggedServiceIds(self::TAG_NAME);
            foreach ($filters as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $extension->addMethodCall('addSelector', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
