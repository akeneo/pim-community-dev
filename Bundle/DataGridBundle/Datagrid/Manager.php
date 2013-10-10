<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
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

    /** @var array */
    protected $rawConfiguration;

    /** @var array */
    protected $processedConfiguration;

    public function __construct(array $rawConfiguration, Builder $builder, SystemAwareResolver $resolver)
    {
        $this->rawConfiguration = $rawConfiguration;
        $this->datagridBuilder  = $builder;
        $this->resolver         = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid($name)
    {
        $config = $this->getConfigurationForGrid($name);
        $datagrid = $this->getDatagridBuilder()->build($name, $config);

        return $datagrid;
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

    /**
     * Returns prepared config for requested datagrid
     * Throws exception in case when datagrid configuration not found
     * Cache prepared config in case if datagrid requested few times
     *
     * @param string $name
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getConfigurationForGrid($name)
    {
        if (!isset($this->rawConfiguration[$name])) {
            throw new \RuntimeException(sprintf('Configuration for datagrid "%s" not found', $name));
        }

        if (!isset($this->processedConfiguration[$name])) {
            $result = $this->rawConfiguration[$name];

            $this->processedConfiguration[$name] = $this->resolver->resolve($name, $result);
        }

        return $this->processedConfiguration[$name];
    }
}
