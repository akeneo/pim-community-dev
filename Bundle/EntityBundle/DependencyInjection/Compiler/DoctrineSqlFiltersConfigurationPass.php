<?php

namespace Oro\Bundle\EntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineSqlFiltersConfigurationPass implements CompilerPassInterface
{
    const TAG = 'oro_entity.orm.sql_filter';
    const FILTERS_SERVICE_KEY = 'oro_entity.orm.sql_filter_collection';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FILTERS_SERVICE_KEY)) {
            return;
        }
        $filtersDefinition = $container->getDefinition(self::FILTERS_SERVICE_KEY);
        $filters = $this->loadFilters($container);
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

    /**
     * @param ContainerBuilder $container
     * @return array
     * @throws \LogicException
     */
    protected function loadFilters(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        $filters = array();
        $enabled = false;
        $names = array();
        $name = '';
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes['filter_name'])) {
                    throw new \LogicException(
                        sprintf('Attribute filter_name is required for %s service', $id)
                    );
                }
                if (in_array($attributes['filter_name'], $names)) {
                    throw new \LogicException(
                        sprintf(
                            'Attribute filter_name "%s" for %s service is already used',
                            $id,
                            $attributes['filter_name']
                        )
                    );
                }
                $name = $attributes['filter_name'];
                $names[] = $name;
                if (!empty($attributes['enabled'])) {
                    $enabled = (bool)$attributes['enabled'];
                }
            }
            $filters[] = array('id' => $id, 'enabled' => $enabled, 'filter_name' => $name);
        }

        return $filters;
    }
}
