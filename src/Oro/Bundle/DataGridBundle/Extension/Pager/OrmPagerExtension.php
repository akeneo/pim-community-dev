<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\Orm\Pager;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;

/**
 * Class OrmPagerExtension
 * @package Oro\Bundle\DataGridBundle\Extension\Pager
 *
 * Responsibility of this extension is to apply pagination on query for ORM datasource
 */
class OrmPagerExtension extends AbstractExtension
{
    /**
     * Query params
     */
    const PAGER_ROOT_PARAM = '_pager';
    const PAGE_PARAM = '_page';
    const PER_PAGE_PARAM = '_per_page';

    const TOTAL_PARAM = 'totalRecords';

    /** @var Pager */
    protected $pager;

    public function __construct(Pager $pager, RequestParameters $requestParams)
    {
        $this->pager = $pager;
        parent::__construct($requestParams);
    }

    /**
     * Prototype object
     */
    public function __clone()
    {
        $this->pager = clone $this->pager;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        /** @TODO disabled when hidden on toolbar */
        // enabled by default for ORM datasource
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        $this->pager->setQueryBuilder($datasource->getQueryBuilder());
        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsIterableObject $result)
    {
        $result->offsetAddToArray('options', [self::TOTAL_PARAM => $this->pager->getNbResults()]);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        $state = [
            'currentPage' => $this->getOr(self::PAGE_PARAM, 1),
            'pageSize'    => $this->getOr(self::PER_PAGE_PARAM, $defaultPerPage)
        ];

        $data->offsetAddToArray('state', $state);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        // Pager should proceed closest to end of accepting chain
        return -240;
    }

    /**
     * Get param or return specified default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName, $default = null)
    {
        $pagerParameters = $this->requestParams->get(self::PAGER_ROOT_PARAM);

        return isset($pagerParameters[$paramName]) ? $pagerParameters[$paramName] : $default;
    }
}
