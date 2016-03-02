<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;

/**
 * Class Manager
 *
 * @package Oro\Bundle\DataGridBundle\Datagrid
 *
 * Responsibility of this class is to store raw config data, prepare configs for datagrid builder.
 * Public interface returns datagrid object prepared by builder using config
 */
class Manager implements ManagerInterface
{
    /** @var Builder */
    protected $datagridBuilder;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var ConfigurationProviderInterface */
    protected $configurationProvider;

    /**
     * Constructor
     *
     * @param ConfigurationProviderInterface $configurationProvider
     * @param Builder                        $builder
     * @param RequestParameters              $requestParams
     */
    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        Builder $builder,
        RequestParameters $requestParams
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->datagridBuilder       = $builder;
        $this->requestParams         = $requestParams;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid($name)
    {
        // prepare for work with current grid
        $this->requestParams->setRootParameter($name);

        $config = $this->getConfigurationForGrid($name);

        $datagrid = $this->getDatagridBuilder()->build($config);

        return $datagrid;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationForGrid($name)
    {
        return $this->configurationProvider->getConfiguration($name);
    }

    /**
     * Internal getter for builder
     *
     * @return Builder
     */
    protected function getDatagridBuilder()
    {
        return $this->datagridBuilder;
    }
}
