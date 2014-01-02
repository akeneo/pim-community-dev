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
 */
class AddParametersToGridListener
{
    /** @var array */
    protected $paramNames;

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param array             $paramNames    Parameter name that should be binded to query
     * @param RequestParameters $requestParams Request params
     */
    public function __construct($paramNames, RequestParameters $requestParams)
    {
        $this->paramNames    = $paramNames;
        $this->requestParams = $requestParams;
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
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();
            $queryParameters = array();
            foreach ($this->paramNames as $paramName) {
                $queryParameters[$paramName]= $this->requestParams->get($paramName, null);
            }
            $queryBuilder->setParameters($queryParameters);
        }
    }
}
