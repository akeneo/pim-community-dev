<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

class FieldConfigGridListener extends AbstractConfigGridListener
{
    const GRID_NAME = 'entityfields-grid';

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQuery();

            $this->prepareQuery($queryBuilder, 'cf', 'cfv_', PropertyConfigContainer::TYPE_FIELD);
        }
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $this->doBuildBefore($event, 'cfv_', PropertyConfigContainer::TYPE_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(QueryBuilder $query, $rootAlias, $alias, $itemsType)
    {
        $query->where($rootAlias.'.mode <> :mode');
        $query->setParameter('mode', ConfigModelManager::MODE_HIDDEN);
        $query->innerJoin($rootAlias.'.entity', 'ce', 'WITH', 'ce.id=' . $this->entityId);
        $query->addSelect('ce.id as entity_id', true);

        return parent::prepareQuery($query, $rootAlias, $alias, $itemsType);
    }


}
