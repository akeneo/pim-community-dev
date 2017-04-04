<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;

/**
 * Abstract pager extension, storage agnostic
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PagerExtension extends AbstractExtension
{
    /** @staticvar string Query params */
    const PAGER_ROOT_PARAM = '_pager';
    const PAGE_PARAM = '_page';
    const PER_PAGE_PARAM = '_per_page';
    const TOTAL_PARAM = 'totalRecords';

    /** @var PagerResolver */
    protected $pagerResolver;

    /**
     * @param PagerResolver     $resolver
     * @param RequestParameters $requestParams
     */
    public function __construct(PagerResolver $resolver, RequestParameters $requestParams)
    {
        $this->pagerResolver = $resolver;
        parent::__construct($requestParams);
    }

    /**
     * Prototype object
     */
    public function __clone()
    {
        $this->pagerResolver = clone $this->pagerResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return 'product-grid' !== $config->offsetGetByPath('name');
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        $pager = $this->getPager($config);

        $pager->setQueryBuilder($datasource->getQueryBuilder());
        $pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $pager->init();
    }

    /**
     * {@inheritdoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
        $result->offsetAddToArray('options', [self::TOTAL_PARAM => $this->getPager($config)->getNbResults()]);
    }

    /**
     * {@inheritdoc}
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
     * Should pass at the very end (after filters and sorters)
     *
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -300;
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
     * @param DatagridConfiguration $config
     *
     * @return PagerInterface
     */
    protected function getPager(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return $this->pagerResolver->getPager($datasourceType);
    }
}
