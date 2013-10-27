<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Provider\SystemAwareResolver;

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

    /** @var array */
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
        $config = $this->getConfigurationForGrid($name);

        // prepare for work with current grid
        $this->requestParams->setRootParameter($name);
        $datagrid = $this->getDatagridBuilder()->build($name, $config);

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
            $config = $this->rawConfiguration[$name];

            $this->processedConfiguration[$name] = $this->resolver->resolve($name, $config);
        }

        return $this->processedConfiguration[$name];
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
