<?php

namespace Oro\Bundle\EntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DatagridConfigurationPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_grid.datagrid.manager';
    const PARAM    = 'class';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($tags as $id => $tag) {
            if (!isset($tag[0]['entity_name'])) {
                continue;
            }

            /** @var Definition $service */
            $service = $container->getDefinition($id);

            $className = $service->getClass();
            if (strpos($className, '%') !== false) {
                $className = $container->getParameter(substr($className, 1, strlen($className) - 2));
            }

            $class = new \ReflectionClass($className);

            if (strpos($class->getParentClass(), 'AbstractDatagrid') !== false) {
                $service
                    ->addArgument($container->getDefinition('oro_entity_config.config_manager'))
//                    ->addMethodCall(
//                        'addDynamicFields',
//                        array()
//                    )
                ;
            }
        }
    }
}
