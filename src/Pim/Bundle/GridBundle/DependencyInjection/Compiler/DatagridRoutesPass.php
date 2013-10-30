<?php

namespace Pim\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\GridBundle\Route\DatagridRouteRegistry;

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
    const REGISTRY_SERVICE = 'pim_grid.routes_registry.builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);
        $registryService = $container->getDefinition(self::REGISTRY_SERVICE);

        foreach ($tags as $tag) {
            $cacheDisabled = isset($tag[0]['cache_disabled']) && $tag[0]['cache_disabled'] === true;
            if (isset($tag[0]['datagrid_name']) && isset($tag[0]['route_name']) && !$cacheDisabled) {
                $registryService->addMethodCall('addRoute', array($tag[0]['datagrid_name'], $tag[0]['route_name']));
            }
        }

        $cacheFile = sprintf('%s/%s', $container->getParameter('kernel.cache_dir'), DatagridRouteRegistry::CACHE_FILE);
        if (is_file($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
