<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Pim\Bundle\DataGridBundle\Datasource\MongoDB\OdmDatasource as PimOdmDatasource;

/**
 * Odm pager extension
 *
 * TODO : lot of common code with OrmPagerExtension that could be move in storage agnostic pager 
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OdmPagerExtension extends AbstractExtension
{
    /**
     * Query params
     */
    const PAGER_ROOT_PARAM = '_pager';
    const PAGE_PARAM       = '_page';
    const PER_PAGE_PARAM   = '_per_page';

    const TOTAL_PARAM = 'totalRecords';

    /** @var PagerInterface */
    protected $pager;

    /**
     * @param PagerInterface    $pager
     * @param RequestParameters $requestParams
     */
    public function __construct(PagerInterface $pager, RequestParameters $requestParams)
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
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $this->matchDatasource($config);
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
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
        $result->offsetAddToArray('options', [self::TOTAL_PARAM => $this->pager->getNbResults()]);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        $state = [
            'currentPage' => $this->getOr(self::PAGE_PARAM, 1),
            'pageSize'    => $this->getOr(self::PER_PAGE_PARAM, $defaultPerPage)
        ];

        $data->offsetAddToArray('state', $state);
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return boolean
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == PimOdmDatasource::TYPE;
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

    /**
     * Should pass at the very end (after filters and sorters)
     *
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -300;
    }
}
