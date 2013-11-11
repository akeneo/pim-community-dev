<?php

namespace Oro\Bundle\SearchBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Extension\SearchDatasource;
use Oro\Bundle\SearchBundle\Query\Query;

class SearchResultsGridListener
{
    /** @var  RequestParameters */
    protected $requestParams;

    /** @var string */
    protected $paramName;

    /** @var Indexer */
    protected $indexer;

    /**
     * @param RequestParameters $requestParams
     * @param Indexer $indexer
     */
    public function __construct(RequestParameters $requestParams, Indexer $indexer)
    {
        $this->requestParams = $requestParams;
        $this->indexer = $indexer;
    }

    /**
     * Adjust query for tag-results-grid (tag search result grid)
     * after datasource has been built
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof SearchDatasource) {
            /** @var $query Query */
            $query = new IndexerQuery(
                $this->indexer,
                $this->indexer->select()
            );

            $searchEntity = $this->requestParams->get('from', '*');
            $searchEntity = empty($searchEntity) ? '*' : $searchEntity;

            $searchString = $this->requestParams->get('search', '');

            $query
                ->from($searchEntity)
                ->andWhere(Indexer::TEXT_ALL_DATA_FIELD, '~', $searchString, 'text');

            $datasource->setQuery($query);
        }
    }
}
