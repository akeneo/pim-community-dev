<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Provider\SystemAwareResolver;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Class Manager
 * @package Oro\Bundle\DataGridBundle\Datagrid
 *
 * Responsibility of this class is to store raw config data, prepare configs for datagrid builder.
 * Public interface returns datagrid object prepared by builder using config
 */
class Manager implements ManagerInterface
{
    /** @var Builder */
    protected $datagridBuilder;

    /** @var SystemAwareResolver */
    protected $resolver;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var array */
    protected $rawConfiguration = [];

    /** @var DatagridConfiguration[] */
    protected $processedConfiguration = [];

    public function __construct(
        array $rawConfiguration,
        Builder $builder,
        SystemAwareResolver $resolver,
        RequestParameters $requestParams
    ) {
        $this->rawConfiguration = $rawConfiguration;
        $this->datagridBuilder  = $builder;
        $this->resolver         = $resolver;
        $this->requestParams    = $requestParams;
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
        if (!isset($this->rawConfiguration[$name])) {
            throw new \RuntimeException(sprintf('Configuration for datagrid "%s" not found', $name));
        }

        if (!isset($this->processedConfiguration[$name])) {
            $config = $this->resolver->resolve($name, $this->rawConfiguration[$name]);

            $this->processedConfiguration[$name] = $config;
        }

        return DatagridConfiguration::createNamed($name, $this->processedConfiguration[$name]);
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
