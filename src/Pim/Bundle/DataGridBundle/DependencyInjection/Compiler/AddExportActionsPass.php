<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Register export actions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddExportActionsPass implements CompilerPassInterface
{
    const EXPORT_ACTION_EXTENSION_ID = 'pim_datagrid.extension.export_action';
    const TAG_NAME                   = 'pim_datagrid.extension.export_action.type';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extension = $container->getDefinition(self::EXPORT_ACTION_EXTENSION_ID);
        if ($extension) {
            $actions = $container->findTaggedServiceIds(self::TAG_NAME);
            foreach ($actions as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $extension->addMethodCall('registerAction', [$tagAttrs['type'], $serviceId]);
            }
        }
    }
}
