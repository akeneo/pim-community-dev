<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SearchBundle\Extension\Pager\IndexerQuery;

class SearchDatasource extends OrmDatasource
{
    const TYPE = 'search';

    /** @var IndexerQuery */
    protected $query;

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $results = $this->query->execute();
        $rows    = [];
        foreach ($results as $result) {
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @param IndexerQuery $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return IndexerQuery
     */
    public function getQuery()
    {
        return $this->query;
    }
}
