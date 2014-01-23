<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

/**
 * Get parameters from request and bind them to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Oro\Bundle\DataGridBundle\EventListener\BaseOrmRelationDatagridListener
 */
class AddParametersToGridListener
{
    /**
     * Included/excluded param names
     * populated by oro/datagrid/column-form-listener on frontend
     */
    const GRID_PARAM_DATA_IN     = 'data_in';
    const GRID_PARAM_DATA_NOT_IN = 'data_not_in';

    /** @var array */
    protected $paramNames;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var boolean */
    protected $isEditMode;

    /**
     * @param array             $paramNames    Parameter name that should be binded to query
     * @param RequestParameters $requestParams Request params
     * @param boolean           $isEditMode    Whether or not to add data_in, data_not_in params to query
     */
    public function __construct($paramNames, RequestParameters $requestParams, $isEditMode = false)
    {
        $this->paramNames    = $paramNames;
        $this->requestParams = $requestParams;
        $this->isEditMode    = $isEditMode;
    }

    /**
     * Bound parameters in query builder
    *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryParameters = $this->prepareParameters();
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();
            $queryBuilder->setParameters($queryParameters);
        }
    }

    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = [];
        foreach ($this->paramNames as $paramName) {
            $queryParameters[$paramName] = $this->requestParams->get($paramName, null);
        }

        if ($this->isEditMode) {
            $params = $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS);

            $queryParameters[self::GRID_PARAM_DATA_IN] = isset($params[self::GRID_PARAM_DATA_IN]) ?
                     $params[self::GRID_PARAM_DATA_IN] : [0];

            $queryParameters[self::GRID_PARAM_DATA_NOT_IN] = isset($params[self::GRID_PARAM_DATA_NOT_IN]) ?
                     $params[self::GRID_PARAM_DATA_NOT_IN] : [0];

            foreach ($this->paramNames as $paramName) {
                if (isset($params[$paramName])) {
                    $queryParameters[$paramName] = $params[$paramName];
                }
            }
        }

        return $queryParameters;
    }
}
