<?php

namespace Oro\Bundle\DataGridBundle\Event;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BuildBefore
 * @package Oro\Bundle\DataGridBundle\Event
 *
 * This event dispatched before datagrid builder starts build datagrid
 * Listeners could apply validation of config and provide changes of config
 */
class BuildBefore extends Event implements GridEventInterface
{
    const NAME = 'oro_datagrid.datgrid.build.before';

    /** @var DatagridInterface */
    protected $datagrid;

    /** @var DatagridConfiguration */
    protected $config;

    public function __construct(DatagridInterface $datagrid, DatagridConfiguration $config)
    {
        $this->datagrid = $datagrid;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }

    /**
     * Getter for datagrid config
     *
     * @return DatagridConfiguration
     */
    public function getConfig()
    {
        return $this->config;
    }
}
