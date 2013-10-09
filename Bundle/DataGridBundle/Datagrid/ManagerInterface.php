<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

interface ManagerInterface
{
    /**
     * Builds datagrid from configuration using builder
     *
     * @return DatagridInterface
     */
    public function getDatagrid();

    /**
     * Returns datagrid builder
     *
     * @return Builder
     */
    public function getDatagridBuilder();
}
