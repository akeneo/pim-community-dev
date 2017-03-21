<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid($name)
    {
        // prepare for work with current grid
        $this->getRequestParameters()->setRootParameter($name);
        $config = $this->getConfigurationForGrid($name);
        $datagrid = $this->getDatagridBuilder()->build($config);

        return $datagrid;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationForGrid($name)
    {
        return $this->getConfigurationProvider()->getConfiguration($name);
    }

    /**
     * Internal getter for builder
     *
     * @return Builder
     */
    final protected function getDatagridBuilder()
    {
        return $this->container->get('oro_datagrid.datagrid.builder');
    }

    /**
     * Internal getter for builder
     *
     * @return ConfigurationProviderInterface
     */
    final protected function getConfigurationProvider()
    {
        return $this->container->get('oro_datagrid.configuration.provider.chain');
    }

    /**
     * Internal getter for builder
     *
     * @return RequestParameters
     */
    final protected function getRequestParameters()
    {
        return $this->container->get('oro_datagrid.datagrid.request_params');
    }
}
