<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

class Builder
{
    const SOURCE_SERVICE_PREFIX = 'oro_grid.source.';

    /** @var string */
    protected $baseDatagridClass;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var DatasourceInterface[] */
    protected $dataSources = array();

    public function __construct($baseDatagridClass, EventDispatcher $eventDispatcher)
    {
        $this->baseDatagridClass = $baseDatagridClass;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * Create, configure and build datagrid
     *
     * @param array $config
     *
     * @return DatagridInterface
     */
    public function build(array $config)
    {
        $class    = $this->getBaseDatagridClass($config);
        $datagrid = new $class();

        $event = new BuildBefore($datagrid, $config);
        $this->eventDispatcher->dispatch(BuildBefore::NAME, $event);
        $config = $event->getConfig();

        $this->buildDataSource($datagrid, $config);

        $event = new BuildAfter($datagrid);
        $this->eventDispatcher->dispatch(BuildBefore::NAME, $event);

        return $datagrid;
    }

    protected function buildDataSource(DatagridInterface $grid, array $config)
    {
        if (!isset($config['source'], $config['source']['type'])) {
            throw new \RuntimeException('Datagrid source does not configured');
        }

        if (!isset($c))
    }

    /**
     * Check whenever need custom datagrid class
     *
     * @param array $config
     *
     * @return string
     */
    protected function getBaseDatagridClass(array $config)
    {
        return !empty($config['options']['base_datagrid_class'])
            ? $config['options']['base_datagrid_class'] : $this->baseDatagridClass;
    }
}
