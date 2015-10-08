<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

interface ManagerInterface
{
    /**
     * Returns prepared datagrid object for further operations
     *
     * @param string $name
     *
     * @return DatagridInterface
     */
    public function getDatagrid($name);

    /**
     * Returns prepared config for requested datagrid
     * Throws exception in case when datagrid configuration not found
     *
     * @param string $name
     *
     * @throws \RuntimeException
     * @return DatagridConfiguration
     */
    public function getConfigurationForGrid($name);
}
