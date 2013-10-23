<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class RelatedUsersGridListener
{
    const GRID_PARAM_DATA_IN     = 'data_in';
    const GRID_PARAM_DATA_NOT_IN = 'data_not_in';

    /** @var  RequestParameters */
    protected $requestParams;

    /** @var string */
    protected $paramName;

    /**
     * @param RequestParameters $requestParams
     * @param string $paramName entity param name
     */
    public function __construct(RequestParameters $requestParams, $paramName)
    {
        $this->requestParams = $requestParams;
        $this->paramName = $paramName;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();
            $queryBuilder->setParameters(
                array(
                    $this->paramName => $this->requestParams->get($this->paramName, null),
                    'data_in'     => $this->requestParams->get(self::GRID_PARAM_DATA_IN, array(0)),
                    'data_not_in' => $this->requestParams->get(self::GRID_PARAM_DATA_NOT_IN, array(0)),
                )
            );
        }
    }
}
