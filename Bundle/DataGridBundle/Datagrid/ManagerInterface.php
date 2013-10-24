<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

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
     * Cache prepared config in case if datagrid requested few times
     *
     * @param string $name
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getConfigurationForGrid($name);
}
