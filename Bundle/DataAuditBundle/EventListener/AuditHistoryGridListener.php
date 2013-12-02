<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

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
     * Used only for auditfield-log-grid grid (subscribed in services.yml)
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $fieldName = $this->requestParams->get(self::GRID_PARAM_FIELD_NAME, false);
        $config->offsetSetByPath('[columns][diffs][context][field_name]', $fieldName);
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();

            $queryParameters = array(
                'objectClass' => str_replace('_', '\\', $this->requestParams->get(self::GRID_PARAM_CLASS, '')),
            );

            if (in_array('objectId', $this->paramsToBind)) {
                $queryParameters['objectId'] = $this->requestParams->get(self::GRID_PARAM_OBJECT_ID, 0);
            }

            $fieldName = $this->requestParams->get(self::GRID_PARAM_FIELD_NAME, false);
            if (!empty($fieldName) && in_array('fieldName', $this->paramsToBind)) {
                $queryParameters['fieldName'] = $fieldName;
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}
