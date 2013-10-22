<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class RoleUsersGridListener
{
    const GRID_PARAM_ROLE_ID     = 'role_id';
    const GRID_PARAM_DATA_IN     = 'data_in';
    const GRID_PARAM_DATA_NOT_IN = 'data_not_in';

    /** @var  RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();
            $queryBuilder->setParameters(
                array(
                    'role_id'     => $this->requestParams->get(self::GRID_PARAM_ROLE_ID, NULL),
                    'data_in'     => $this->requestParams->get(self::GRID_PARAM_DATA_IN, array(0)),
                    'data_not_in' => $this->requestParams->get(self::GRID_PARAM_DATA_NOT_IN, array(0)),
                )
            );
        }
    }
} 