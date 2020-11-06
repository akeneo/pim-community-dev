<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

interface ManagerInterface
{
    /**
     * Returns prepared datagrid object for further operations
     *
     * @param string $name
     */
    public function getDatagrid(string $name): DatagridInterface;

    /**
     * Returns prepared config for requested datagrid
     * Throws exception in case when datagrid configuration not found
     *
     * @param string $name
     *
     * @throws \RuntimeException
     */
    public function getConfigurationForGrid(string $name): DatagridConfiguration;
}
