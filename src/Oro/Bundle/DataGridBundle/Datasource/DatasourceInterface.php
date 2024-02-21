<?php

namespace Oro\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

/**
 * Class DatasourceInterface
 * @package Oro\Bundle\DataGridBundle\Datasource
 */
interface DatasourceInterface
{
    /**
     * Add source to datagrid
     *
     * @param DatagridInterface $grid
     * @param array             $config
     */
    public function process(DatagridInterface $grid, array $config);

    /**
     * Returns data extracted via datasource
     *
     * @return array
     */
    public function getResults();
}
