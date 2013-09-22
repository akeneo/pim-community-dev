<?php

namespace Oro\Bundle\EntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineSqlFiltersConfigurationPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_entity.orm.sql_filter';
    const FILTER_COLLECTION_SERVICE_NAME = 'oro_entity.orm.sql_filter_collection';
    const ENTITY_MANAGER_SERVICE_NAME = 'doctrine.orm.entity_manager';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FILTER_COLLECTION_SERVICE_NAME)) {
            return;
        }

        $em = $container->findDefinition(self::ENTITY_MANAGER_SERVICE_NAME);
        $em->addMethodCall('setFilterCollection', array(new Reference(self::FILTER_COLLECTION_SERVICE_NAME)));

        $collectionDef = $container->getDefinition(self::FILTER_COLLECTION_SERVICE_NAME);
        foreach ($this->loadFilters($container) as $filter) {
            $collectionDef->addMethodCall(
                'addFilter',
                array($filter['filter_name'], new Reference($filter['id']))
            );
            if ($filter['enabled']) {
                $collectionDef->addMethodCall('enable', array($filter['filter_name']));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @return array
     * @throws \LogicException
     */
    protected function loadFilters(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);
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
