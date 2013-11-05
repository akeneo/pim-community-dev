<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class RelatedUsersGridListener
{
    const GRID_PARAM_DATA_IN     = 'data_in';
    const GRID_PARAM_DATA_NOT_IN = 'data_not_in';

    /** @var  RequestParameters */
    protected $requestParams;

    /** @var string */
    protected $paramName;

    /** @var boolean */
    protected $isCheckboxes;

    /**
     * @param RequestParameters $requestParams
     * @param string $paramName entity param name
     * @param bool $isCheckboxes whether or not to add data_in, data_not_in params to query
     */
    public function __construct(RequestParameters $requestParams, $paramName, $isCheckboxes = true)
    {
        $this->requestParams = $requestParams;
        $this->paramName = $paramName;
        $this->isCheckboxes = $isCheckboxes;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();

            $queryParameters = array(
                $this->paramName => $this->requestParams->get($this->paramName, null),
                'data_in'     => $this->requestParams->get(self::GRID_PARAM_DATA_IN, array(0)),
                'data_not_in' => $this->requestParams->get(self::GRID_PARAM_DATA_NOT_IN, array(0)),
            );

            if (!$this->isCheckboxes) {
                unset($queryParameters['data_in'], $queryParameters['data_not_in']);
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}
