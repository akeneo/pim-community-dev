<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\Orm\Pager;

class OrmPagerExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const PAGER_OPTION_PATH                  = '[pager]';
    const PAGER_ENABLE_OPTION_PATH           = '[pager][enabled]';
    const PAGER_DEFAULT_PER_PAGE_OPTION_PATH = '[pager][default_per_page]';

    /**
     * Query params
     */
    const PAGER_ROOT_PARAM = '_pager';
    const PAGE_PARAM       = '_page';
    const PER_PAGE_PARAM   = '_per_page';

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
    public function isApplicable(array $config)
    {
        $enabled = $this->accessor->getValue($config, Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE
            && $this->accessor->getValue($config, self::PAGER_ENABLE_OPTION_PATH) !== false;

        $this->validateConfiguration(
            new Configuration(),
            array_intersect_key($config, array_flip(['pager']))
        );

        return $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $this->accessor->getValue($config, self::PAGER_DEFAULT_PER_PAGE_OPTION_PATH) ? : 10;

        $this->pager->setQueryBuilder($datasource->getQuery());
        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        $result->options                    = isset($result->options) ? $result->options : [];
        $result->options[self::TOTAL_PARAM] = $this->pager->getNbResults();
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $result)
    {
        // TODO: Implement visitMetadata() method.
    }

    /**
     * Pager should proceed in the end of accepting chain
     *
     * @return int
     */
    public function getPriority()
    {
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
