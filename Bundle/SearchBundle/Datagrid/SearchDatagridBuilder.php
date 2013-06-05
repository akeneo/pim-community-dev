<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Builder\AbstractDatagridBuilder;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\SearchBundle\Datagrid\IndexerPager;

class SearchDatagridBuilder extends AbstractDatagridBuilder
{
    /**
     * @param  ProxyQueryInterface $query
     * @return PagerInterface
     */
    protected function createPager(ProxyQueryInterface $query)
    {
        $pager = new IndexerPager();
        $pager->setQuery($query);

        return $pager;
    }
}
