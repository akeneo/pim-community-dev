<?php

namespace Pim\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers routes for the datagrids
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRoutesPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_grid.datagrid.manager';
    const REGISTRY_SERVICE = 'pim_grid.routes_registry';

    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);
        $registryService = $container->getDefinition(self::REGISTRY_SERVICE);

        foreach ($tags as $id => $tag) {
            if (isset($tag[0]['datagrid_name']) && isset($tag[0]['route_name'])) {
                $registryService->addMethodCall('addRoute', array($tag[0]['datagrid_name'], $tag[0]['route_name']));
            }
        }
    }
}
