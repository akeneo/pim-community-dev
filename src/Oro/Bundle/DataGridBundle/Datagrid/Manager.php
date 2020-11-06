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
    private $datagridBuilder;

    /** @var ConfigurationProviderInterface */
    private $configurationProvider;

    /** @var RequestParameters */
    private $requestParameters;

    public function __construct(
        Builder $datagridBuilder,
        ConfigurationProviderInterface $configurationProvider,
        RequestParameters $requestParameters
    ) {
        $this->datagridBuilder= $datagridBuilder;
        $this->configurationProvider = $configurationProvider;
        $this->requestParameters = $requestParameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid(string $name): DatagridInterface
    {
        // prepare for work with current grid
        $this->requestParameters->setRootParameter($name);
        $config = $this->getConfigurationForGrid($name);

        return $this->datagridBuilder->build($config);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationForGrid(string $name): DatagridConfiguration
    {
        return $this->configurationProvider->getConfiguration($name);
    }
}
