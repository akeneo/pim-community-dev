<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class AuditHistoryGridListener
{
    const GRID_PARAM_CLASS      = 'object_class';
    const GRID_PARAM_OBJECT_ID  = 'object_id';
    const GRID_PARAM_FIELD_NAME = 'field_name';

    /** @var  RequestParameters */
    protected $requestParams;

    /** @var array */
    protected $paramsToBind = [];

    /**
     * @param RequestParameters $requestParams
     * @param array $paramsToBind
     */
    public function __construct(RequestParameters $requestParams, $paramsToBind = [])
    {
        $this->requestParams = $requestParams;
        $this->paramsToBind = $paramsToBind;
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
            );

            if (isset($this->paramsToBind['objectId'])) {
                $queryParameters['objectId'] = $this->requestParams->get(self::GRID_PARAM_OBJECT_ID, 0);
            }

            $fieldName = $this->requestParams->get(self::GRID_PARAM_FIELD_NAME, false);
            if (!empty($fieldName) && isset($this->paramsToBind['fieldName'])) {
                $queryParameters['fieldName'] = $fieldName;
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}
