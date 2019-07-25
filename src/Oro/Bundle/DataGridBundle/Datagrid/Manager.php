<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

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
    public function getDatagrid($name)
    {
        // prepare for work with current grid
        $this->requestParameters->setRootParameter($name);
        $config = $this->getConfigurationForGrid($name);
        $datagrid = $this->datagridBuilder->build($config);

        return $datagrid;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationForGrid($name)
    {
        return $this->configurationProvider->getConfiguration($name);
    }
}
