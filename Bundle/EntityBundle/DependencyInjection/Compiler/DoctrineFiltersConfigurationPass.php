<?php

namespace Oro\Bundle\EntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineFiltersConfigurationPass implements CompilerPassInterface
{
    const TAG = 'oro_entity.sql_filter';
    const FILTERS_SERVICE_KEY = 'oro_entity.orm.query.filter_collection';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FILTERS_SERVICE_KEY)) {
            return;
        }
        $filtersDefinition = $container->getDefinition(self::FILTERS_SERVICE_KEY);

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        $filters = array();
        $enabled = false;
        $name = self::TAG . rand(0, 1000);
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!empty($attributes['enabled'])) {
                    $enabled = (bool)$attributes['enabled'];
                }
                if (!empty($attributes['filter_name'])) {
                    $name = $attributes['filter_name'];
                }
            }
            $filters[] = array('id' => $id, 'enabled' => $enabled, 'filter_name' => $name);
        }
        foreach ($filters as $filter) {
            $filtersDefinition->addMethodCall(
                'addFilter',
                array(
                    $filter['filter_name'],
                    new Reference($filter['id'])
                )
            );
            if ($filter['enabled']) {
                $filtersDefinition->addMethodCall('enable', array($filter['filter_name']));
            }
        }
        $em = $container->findDefinition('doctrine.orm.entity_manager');
        $em->addMethodCall('setFilterCollection', array(new Reference(self::FILTERS_SERVICE_KEY)));
    }
}
