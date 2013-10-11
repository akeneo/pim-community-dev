<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

class OrmPagerExtension implements ExtensionVisitorInterface
{
    const PAGER_KEY            = 'pager';
    const ENABLED_KEY          = 'enabled';
    const DEFAULT_PER_PAGE_KEY = 'default_per_page';

    const PAGER_ROOT_PARAM = '_pager';
    const PAGE_PARAM       = '_page';
    const PER_PAGE_PARAM   = '_per_page';

    const TOTAL_PARAM = 'totalRecords';

    /** @var PagerInterface */
    protected $pager;

    /** @var RequestParameters */
    protected $requestParams;

    public function __construct(PagerInterface $pager, RequestParameters $requestParams)
    {
        $this->pager         = $pager;
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $enabled = $config[OrmDatasource::SOURCE_KEY][OrmDatasource::TYPE_KEY] == OrmDatasource::TYPE;

        if (isset($config[self::PAGER_KEY][self::ENABLED_KEY])) {
            $enabled = $enabled && (bool)$config[self::PAGER_KEY][self::ENABLED_KEY];
        }

        return $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = !empty($config[self::PAGER_KEY][self::DEFAULT_PER_PAGE_KEY]) ?
            $config[self::PAGER_KEY][self::DEFAULT_PER_PAGE_KEY] : 10;

        $this->pager->setQuery($datasource->getQuery());
        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        $result->options = isset($result->options) ? $result->options : array();

        $result->options[self::TOTAL_PARAM] = $this->pager->getNbResults();
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
