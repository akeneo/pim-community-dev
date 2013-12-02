<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class FieldConfigGridListener extends AbstractConfigGridListener
{
    const GRID_NAME = 'entityfields-grid';
    const ENTITY_PARAM = 'entityId';

    /** @var  RequestParameters */
    protected $requestParams;

    /**
     * @param ConfigManager $configManager
     * @param RequestParameters $requestParams
     */
    public function __construct(ConfigManager $configManager, RequestParameters $requestParams)
    {
        parent::__construct($configManager);

        $this->requestParams = $requestParams;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQueryBuilder();

            $this->prepareQuery($queryBuilder, 'cf', 'cfv_', PropertyConfigContainer::TYPE_FIELD);
        }
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        // false flag used to place dynaic columns to the end of grid
        $this->doBuildBefore($event, 'cfv_', PropertyConfigContainer::TYPE_FIELD, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(QueryBuilder $query, $rootAlias, $alias, $itemsType)
    {
        $entityId = $this->requestParams->get(self::ENTITY_PARAM, 0);

        $query->where($rootAlias.'.mode <> :mode');
        $query->setParameter('mode', ConfigModelManager::MODE_HIDDEN);
        $query->innerJoin($rootAlias.'.entity', 'ce', 'WITH', 'ce.id=' . $entityId);
        $query->addSelect('ce.id as entity_id');

        return parent::prepareQuery($query, $rootAlias, $alias, $itemsType);
    }
}
