<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;


use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

interface MassActionMediatorInterface
{
    /**
     * @return MassActionInterface
     */
    public function getMassAction();

    /**
     * @return IterableResultInterface|[]
     */
    public function getResults();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return DatagridInterface
     */
    public function getDatagrid();
}
