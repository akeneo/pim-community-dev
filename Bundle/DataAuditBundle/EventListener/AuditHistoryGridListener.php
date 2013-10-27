<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class AuditHistoryGridListener
{
    const GRID_PARAM_CLASS     = 'object_class';
    const GRID_PARAM_OBJECT_ID = 'object_id';

    /** @var  RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();

            $queryParameters = array(
                'objectClass' => str_replace('_', '\\', $this->requestParams->get(self::GRID_PARAM_CLASS, '')),
                'objectId'    => $this->requestParams->get(self::GRID_PARAM_OBJECT_ID, 0),
            );

            $queryBuilder->setParameters($queryParameters);
        }
    }
}
