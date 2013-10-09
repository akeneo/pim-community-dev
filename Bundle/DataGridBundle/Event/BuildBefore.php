<?php

namespace Oro\Bundle\DataGridBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

/**
 * Class BuildBefore
 * @package Oro\Bundle\DataGridBundle\Event
 *
 * This event dispatched before datagrid builder starts build datagrid
 * Listeners should apply validation of config and add extensions to datagrid object
 */
class BuildBefore extends Event implements GridEventInterface
{
    const NAME = 'oro_grid.datgrid.build.before';

    /** @var DatagridInterface */
    protected $datagrid;

    /** @var array */
    protected $config;

    public function __construct(DatagridInterface $datagrid, array $config)
    {
        $this->datagrid = $datagrid;
        $this->config   = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }

    /**
     * Getter for datagrid config array
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter for datagrid config
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }
}
