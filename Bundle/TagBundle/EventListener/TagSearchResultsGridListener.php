<?php

namespace Oro\Bundle\TagBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class TagSearchResultsGridListener
{
    /** @var  RequestParameters */
    protected $requestParams;

    /** @var string */
    protected $paramName;

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

            $queryBuilder->setParameter('tag', $this->requestParams->get('tag_id', 0));

            $searchEntity = $this->requestParams->get('from', '*');
            if ($searchEntity != '*') {
                $queryBuilder->andWhere('tt.alias = :alias')
                    ->setParameter('alias', $searchEntity);
            }
        }
    }
}
