<?php

namespace Pim\Bundle\CustomEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Sets custom entity names on datagrid managers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridManagerPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_grid.datagrid.manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($tags as $serviceId=>$tag) {
            if (isset($tag[0]['custom_entity_name'])) {
                $container->getDefinition($serviceId)
                    ->addMethodCall('setCustomEntityName', array($tag[0]['custom_entity_name']));
            }
        }
    }
}
