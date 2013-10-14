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
}
