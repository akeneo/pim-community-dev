<?php

namespace Oro\Bundle\SearchBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Oro\Bundle\SearchBundle\Extension\SearchDatasource;

class SearchPagerExtension extends OrmPagerExtension
{
    /** @var IndexerPager */
    protected $pager;

    public function __construct(IndexerPager $pager, RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
        $this->pager = $pager;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        // enabled by default for search datasource
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == SearchDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        $this->pager->setQuery($datasource->getQuery());

        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();
    }
}
